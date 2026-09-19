<?php

declare(strict_types=1);

use ForgeDashboard\Client;
use ForgeDashboard\Exception\ApiError;
use ForgeDashboard\Generated\ApiException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Attaches Middleware::history() to $stack *after* the Client has already
 * pushed its own auth/retry middleware onto it, so history ends up
 * closest to the transport and records the final, fully mutated request --
 * not the pristine one Client started from. HandlerStack resolves lazily
 * per request, so a middleware pushed after construction still takes
 * effect.
 *
 * @param-out array<array-key, array{request: RequestInterface, response: ResponseInterface|null, error: mixed, options: array<array-key, mixed>}>|ArrayAccess<int, array{request: RequestInterface, response: ResponseInterface|null, error: mixed, options: array<array-key, mixed>}> $history
 */
function attachHistory(HandlerStack $stack, mixed &$history): void
{
    $history = [];
    $stack->push(Middleware::history($history));
}

/**
 * Guzzle's own type for a history container is a loose
 * array<array-key, shape>|ArrayAccess<int, shape> (it has to support
 * both), so nothing statically guarantees a given offset exists or that
 * the container is even a plain array -- Assert::assertIsArray/
 * assertArrayHasKey narrow that for PHPStan (via phpstan/phpstan-phpunit)
 * the same way an `if` would, without resorting to an inline @var
 * override.
 *
 * @param  array<array-key, array{request: RequestInterface, response: ResponseInterface|null, error: mixed, options: array<array-key, mixed>}>|ArrayAccess<int, array{request: RequestInterface, response: ResponseInterface|null, error: mixed, options: array<array-key, mixed>}>  $history
 */
function requestAt(array|ArrayAccess $history, int $index): RequestInterface
{
    Assert::assertIsArray($history);
    Assert::assertArrayHasKey($index, $history);

    return $history[$index]['request'];
}

/**
 * @param  array<array-key, array{request: RequestInterface, response: ResponseInterface|null, error: mixed, options: array<array-key, mixed>}>|ArrayAccess<int, array{request: RequestInterface, response: ResponseInterface|null, error: mixed, options: array<array-key, mixed>}>  $history
 */
function historyCount(array|ArrayAccess $history): int
{
    Assert::assertIsArray($history);

    return count($history);
}

afterEach(function (): void {
    putenv(Client::API_TOKEN_ENV_VAR);
});

it('sends an explicit bearer token on every request', function (): void {
    $mock = new MockHandler([new Response(200, [], '{"version":"dev"}')]);
    $stack = HandlerStack::create($mock);
    $client = new Client('https://example.test', 'from-constructor', handlerStack: $stack);
    attachHistory($stack, $history);

    $client->health->getVersion();

    expect(requestAt($history, 0)->getHeaderLine('Authorization'))->toBe('Bearer from-constructor');
});

it('falls back to the environment variable when no token is passed', function (): void {
    putenv(Client::API_TOKEN_ENV_VAR.'=from-env');

    $mock = new MockHandler([new Response(200, [], '{"version":"dev"}')]);
    $stack = HandlerStack::create($mock);
    $client = new Client('https://example.test', handlerStack: $stack);
    attachHistory($stack, $history);

    $client->health->getVersion();

    expect(requestAt($history, 0)->getHeaderLine('Authorization'))->toBe('Bearer from-env');
});

it('lets an explicit token override the environment variable', function (): void {
    putenv(Client::API_TOKEN_ENV_VAR.'=from-env');

    $mock = new MockHandler([new Response(200, [], '{"version":"dev"}')]);
    $stack = HandlerStack::create($mock);
    $client = new Client('https://example.test', 'from-constructor', handlerStack: $stack);
    attachHistory($stack, $history);

    $client->health->getVersion();

    expect(requestAt($history, 0)->getHeaderLine('Authorization'))->toBe('Bearer from-constructor');
});

it('sends no authorization header when no token is available', function (): void {
    $mock = new MockHandler([new Response(200, [], '{"version":"dev"}')]);
    $stack = HandlerStack::create($mock);
    $client = new Client('https://example.test', handlerStack: $stack);
    attachHistory($stack, $history);

    $client->health->getVersion();

    expect(requestAt($history, 0)->hasHeader('Authorization'))->toBeFalse();
});

it('does not produce a double slash when the base url has a trailing slash', function (): void {
    $mock = new MockHandler([new Response(200, [], '{"version":"dev"}')]);
    $stack = HandlerStack::create($mock);
    $client = new Client('https://example.test/', handlerStack: $stack);
    attachHistory($stack, $history);

    $client->health->getVersion();

    $uri = requestAt($history, 0)->getUri();
    // Pins that setHost() actually ran at all -- the generated
    // Configuration's own default host ('http://localhost') would
    // otherwise silently take over instead.
    expect($uri->getHost())->toBe('example.test');
    expect((string) $uri)->not->toContain('//api');
});

it('uses an explicit http client directly instead of building one', function (): void {
    // No auth or retry middleware pushed onto this handler stack at
    // all -- if Client built its own Guzzle client instead of using
    // this one (ignoring it entirely, say, on a $httpClient ?? ...
    // that got its operands swapped), the request would either 500
    // via a retry loop with nothing to retry into, or simply not be
    // the object this test can see requests through.
    $mock = new MockHandler([new Response(200, [], '{"version":"dev"}')]);
    $history = [];
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($history));
    $explicitClient = new GuzzleClient(['handler' => $stack]);

    $client = new Client('https://example.test', 'a-token', httpClient: $explicitClient);
    $client->health->getVersion();

    expect($history)->toBeArray()->toHaveCount(1);
    // Client's own auth middleware never ran on this stack, since
    // buildGuzzleClient() -- the only place that middleware gets
    // pushed -- must not run when an explicit client is given.
    expect(requestAt($history, 0)->hasHeader('Authorization'))->toBeFalse();
});

it('retries a 5xx response and succeeds on the retry', function (): void {
    $mock = new MockHandler([
        new Response(503, ['Retry-After' => '0']),
        new Response(200, [], '{"version":"dev"}'),
    ]);
    $stack = HandlerStack::create($mock);
    $client = new Client('https://example.test', handlerStack: $stack);
    attachHistory($stack, $history);

    $client->health->getVersion();

    expect(historyCount($history))->toBe(2);
});

it('defaults max retries to exactly three', function (): void {
    // Four failing responses queued, none of them a success: with the
    // real default of 3 retries (4 attempts total), the 4th response
    // is consumed and the walk ends there. A default one lower would
    // give up after 3 attempts (historyCount would be 3, not 4); a
    // default one higher would ask MockHandler for a 5th response it
    // doesn't have, throwing OutOfBoundsException instead of the
    // ApiException this test catches -- either direction fails loudly.
    $mock = new MockHandler([
        new Response(503),
        new Response(503),
        new Response(503),
        new Response(503),
    ]);
    $stack = HandlerStack::create($mock);
    $client = new Client('https://example.test', handlerStack: $stack);
    attachHistory($stack, $history);

    try {
        $client->health->getVersion();
        Assert::fail('expected an ApiException');
    } catch (ApiException) {
        // expected -- fall through to the assertion below
    }

    expect(historyCount($history))->toBe(4);
});

it('never retries a 400', function (): void {
    $mock = new MockHandler([new Response(400, [], '{"error":"nope"}')]);
    $stack = HandlerStack::create($mock);
    $client = new Client('https://example.test', handlerStack: $stack);
    attachHistory($stack, $history);

    try {
        $client->health->getVersion();
        Assert::fail('expected an ApiException');
    } catch (ApiException) {
        // expected -- fall through to the assertion below
    }

    expect(historyCount($history))->toBe(1);
});

it('decodes an error response into an api error', function (): void {
    $mock = new MockHandler([
        new Response(401, [], '{"error":"token expired"}'),
    ]);
    $stack = HandlerStack::create($mock);
    $client = new Client('https://example.test', 'stale-token', handlerStack: $stack, maxRetries: 0);

    try {
        $client->dashboard->getDashboard();
        Assert::fail('expected an ApiException');
    } catch (ApiException $exception) {
        $apiError = ApiError::fromGeneratedException($exception);
    }

    expect($apiError->statusCode)->toBe(401)
        ->and($apiError->getMessage())->toContain('token expired');
});
