<?php

declare(strict_types=1);

namespace ForgeDashboard;

use ForgeDashboard\Generated\Api\AdminApi;
use ForgeDashboard\Generated\Api\AuthApi;
use ForgeDashboard\Generated\Api\DashboardApi;
use ForgeDashboard\Generated\Api\HealthApi;
use ForgeDashboard\Generated\Api\PullRequestsApi;
use ForgeDashboard\Generated\Api\SettingsApi;
use ForgeDashboard\Generated\Api\SharingApi;
use ForgeDashboard\Generated\Api\TokensApi;
use ForgeDashboard\Generated\Api\WebhooksApi;
use ForgeDashboard\Generated\Configuration;
use ForgeDashboard\Retry\RetryMiddleware;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;

/**
 * A forge-dashboard API client (github.com/alrayyes/forge-dashboard).
 *
 * Every operation except {@see HealthApi::health()}, {@see HealthApi::getVersion()}
 * and the two webhook receivers ({@see WebhooksApi}) requires an
 * authenticated session. forge-dashboard accepts either a browser's
 * passkey session cookie or a personal API token
 * (`Authorization: Bearer <token>`, minted via `POST /api/tokens`) -- this
 * client only speaks the token half, the headless-friendly one. Pass it
 * to the constructor or set {@see self::API_TOKEN_ENV_VAR}. See the
 * README for the full explanation and an example.
 *
 * Each generated Api class is exposed as a public, readonly property
 * ({@see self::$dashboard}, {@see self::$tokens}, ...) for direct,
 * one-response-at-a-time calls -- forge-dashboard's spec has no
 * pagination to wrap.
 */
final readonly class Client
{
    /**
     * Environment variable the constructor falls back to when no API
     * token is passed explicitly.
     */
    public const API_TOKEN_ENV_VAR = 'FORGE_DASHBOARD_API_TOKEN';

    public HealthApi $health;

    public DashboardApi $dashboard;

    public AuthApi $auth;

    public SettingsApi $settings;

    public TokensApi $tokens;

    public WebhooksApi $webhooks;

    public PullRequestsApi $pullRequests;

    public AdminApi $admin;

    public SharingApi $sharing;

    /**
     * @param  string  $baseUrl  the forge-dashboard instance's origin, e.g.
     *                           "https://forge-dashboard.example.com"
     * @param  string|null  $apiToken  overrides
     *                                 {@see self::API_TOKEN_ENV_VAR} when given
     * @param  ClientInterface|null  $httpClient  replaces the underlying Guzzle
     *                                            client entirely (a test double, usually); when given, the retry
     *                                            and auth-injection middleware below are *not* applied, and
     *                                            $handlerStack/$maxRetries/$retryBaseDelaySeconds are ignored --
     *                                            the caller owns that behaviour instead
     * @param  HandlerStack|null  $handlerStack  the base handler stack the
     *                                           retry and auth-injection middleware get pushed onto -- pass a
     *                                           stack built around a {@see MockHandler} in a
     *                                           test to exercise both against a fake transport. Defaults to a
     *                                           fresh {@see HandlerStack::create()} (the real cURL/stream
     *                                           handler). Ignored when $httpClient is given.
     * @param  int  $maxRetries  additional attempts after the first, for a
     *                           429/5xx or network failure
     * @param  float  $retryBaseDelaySeconds  the starting backoff before
     *                                        jitter and any server-sent Retry-After
     */
    public function __construct(
        string $baseUrl,
        ?string $apiToken = null,
        ?ClientInterface $httpClient = null,
        ?HandlerStack $handlerStack = null,
        int $maxRetries = 3,
        float $retryBaseDelaySeconds = 0.25,
    ) {
        $token = $apiToken ?? (getenv(self::API_TOKEN_ENV_VAR) ?: null);

        $guzzle = $httpClient ?? $this->buildGuzzleClient($handlerStack ?? HandlerStack::create(), $token, $maxRetries, $retryBaseDelaySeconds);

        $config = new Configuration;
        $config->setHost(rtrim($baseUrl, '/'));

        $this->health = new HealthApi($guzzle, $config);
        $this->dashboard = new DashboardApi($guzzle, $config);
        $this->auth = new AuthApi($guzzle, $config);
        $this->settings = new SettingsApi($guzzle, $config);
        $this->tokens = new TokensApi($guzzle, $config);
        $this->webhooks = new WebhooksApi($guzzle, $config);
        $this->pullRequests = new PullRequestsApi($guzzle, $config);
        $this->admin = new AdminApi($guzzle, $config);
        $this->sharing = new SharingApi($guzzle, $config);
    }

    private function buildGuzzleClient(HandlerStack $stack, ?string $token, int $maxRetries, float $baseDelaySeconds): GuzzleClient
    {
        $stack->push(Middleware::mapRequest(
            static function (RequestInterface $request) use ($token): RequestInterface {
                if ($token === null || $token === '') {
                    return $request;
                }

                return $request->withHeader('Authorization', 'Bearer '.$token);
            },
        ), 'bearer_auth');

        $retry = new RetryMiddleware($maxRetries, $baseDelaySeconds);
        $stack->push(Middleware::retry($retry->decider(), $retry->delay()), 'retry');

        return new GuzzleClient(['handler' => $stack]);
    }
}
