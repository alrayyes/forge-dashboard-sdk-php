<?php

declare(strict_types=1);

use ForgeDashboard\Exception\ApiError;
use ForgeDashboard\Generated\ApiException;
use ForgeDashboard\Generated\Model\Error as GeneratedError;

it('prefers the deserialized error model when one was attached', function (): void {
    $exception = new ApiException('[401] ...', 401, [], '{"error":"session expired"}');
    $exception->setResponseObject(new GeneratedError(['error' => 'session expired']));

    $apiError = ApiError::fromGeneratedException($exception);

    expect($apiError->statusCode)->toBe(401)
        ->and($apiError->getMessage())->toContain('session expired');
});

it('decodes the raw body when no model was attached', function (): void {
    // The status code the spec doesn't document explicitly for this
    // operation (a 429, say) -- the generated Api classes never
    // deserialize those, so ApiError has to decode the JSON itself.
    $exception = new ApiException('[429] ...', 429, [], '{"error":"slow down"}');

    $apiError = ApiError::fromGeneratedException($exception);

    expect($apiError->statusCode)->toBe(429)
        ->and($apiError->getMessage())->toContain('slow down');
});

it('falls back to the raw body when the response object is missing getError', function (): void {
    // decodeMessage() requires getError() specifically before trusting a
    // response object -- one without it has to fall through to decoding
    // the raw JSON body instead, same as no response object at all.
    $exception = new ApiException('[500] boom', 500, [], '{"error":"from body"}');
    $exception->setResponseObject(new class
    {
        public function getCode(): string
        {
            return 'from_object';
        }
    });

    $apiError = ApiError::fromGeneratedException($exception);

    expect($apiError->getMessage())->toContain('from body');
});

it("falls back gracefully when the body isn't the expected shape", function (): void {
    $exception = new ApiException('[500] boom', 500, [], 'not json at all');

    $apiError = ApiError::fromGeneratedException($exception);

    expect($apiError->statusCode)->toBe(500)
        ->and($apiError->getMessage())->toContain('[500] boom');
});

it('formats status code and message', function (): void {
    $apiError = new ApiError(500, 'something broke');

    expect($apiError->getMessage())->toBe('forge-dashboard: 500: something broke');
});
