<?php

declare(strict_types=1);

namespace ForgeDashboard\Tests\Contract;

use ForgeDashboard\Client;
use ForgeDashboard\Generated\Model\Dashboard;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Runs the client against a Prism mock server generated from
 * forge-dashboard's own pinned spec (see ci.yml's `contract` job) --
 * never a hand-rolled stub (rules/sdk-generation.md's "testing against
 * the spec, not a hand-written stub"). This proves the client's requests
 * conform to the spec's shape; it says nothing about whether the real
 * server still matches that spec.
 *
 * Run locally with:
 *   docker run -d -p 4010:4010 -v "$(pwd)/openapi:/spec:ro" \
 *     stoplight/prism:5 mock -h 0.0.0.0 -m false /spec/openapi.yaml
 *
 * @internal
 */
final class ClientTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $baseUrl = getenv('FORGE_DASHBOARD_BASE_URL');
        if ($baseUrl === false) {
            self::markTestSkipped('FORGE_DASHBOARD_BASE_URL must point at a running Prism mock');
        }

        $this->client = new Client($baseUrl, 'prism-does-not-check-this');
    }

    #[Test]
    public function fetches_the_servers_version(): void
    {
        // The spec gives Version.version a real example ("0.12.1"), which
        // Prism fills the mock response with -- a deterministic value to
        // assert on rather than just a type check PHPStan already knows
        // is always true.
        self::assertSame('0.12.1', $this->client->health->getVersion()->getVersion());
    }

    #[Test]
    public function reports_healthy(): void
    {
        self::assertSame('ok', $this->client->health->health()->getStatus());
    }

    #[Test]
    public function calls_an_authenticated_endpoint(): void
    {
        // The instanceof below narrows away the spec's documented Error
        // alternative response for this operation -- a real runtime
        // check, unlike asserting non-null on a field PHPStan already
        // knows Dashboard declares as required, which is why this test
        // doesn't also assert on the payload's contents.
        $dashboard = $this->client->dashboard->getDashboard();

        self::assertInstanceOf(Dashboard::class, $dashboard);
    }
}
