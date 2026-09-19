# forge-dashboard-sdk-php

[![CI](https://github.com/alrayyes/forge-dashboard-sdk-php/actions/workflows/ci.yml/badge.svg)](https://github.com/alrayyes/forge-dashboard-sdk-php/actions/workflows/ci.yml)
[![Codecov](https://codecov.io/gh/alrayyes/forge-dashboard-sdk-php/graph/badge.svg)](https://codecov.io/gh/alrayyes/forge-dashboard-sdk-php)
[![docs](https://img.shields.io/badge/docs-phpdoc-blue)](https://alrayyes.github.io/forge-dashboard-sdk-php/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

A PHP client for [forge-dashboard](https://github.com/alrayyes/forge-dashboard)'s
REST API, generated from its OpenAPI spec with
[openapi-generator](https://openapi-generator.tech/)'s `php` target and a
Guzzle HTTP transport. It saves you from hand-rolling HTTP requests, auth
and retries against the API yourself.

## Requirements

- PHP 8.2 or later, with the `json` extension (bundled by default).
- [Composer](https://getcomposer.org/).
- A running forge-dashboard instance.
- A personal API token for that instance (see "Authentication" below) —
  every endpoint except `health`, `getVersion` and the two webhook
  receivers needs one.

## Installation

```sh
composer require alrayyes/forge-dashboard-sdk-php
```

This package isn't on Packagist yet — see "Publishing" below. Until it is,
require it as a VCS repository pointed at this Git URL, or `composer require`
a specific commit.

## Authentication

forge-dashboard accepts either a browser's passkey session cookie or a
personal API token — `Authorization: Bearer <token>` — as an alternative
that needs no WebAuthn ceremony. This SDK only speaks the token half, the
one a script can actually use. Mint one by signing into the dashboard once
and calling `POST /api/tokens`, then pass it to the constructor or set
`FORGE_DASHBOARD_API_TOKEN` in the environment:

```php
use ForgeDashboard\Client;

$client = new Client(
    'https://forge-dashboard.example.com',
    apiToken: getenv('FORGE_DASHBOARD_API_TOKEN'),
);
```

## Usage

`health` and `getVersion` need no token and are good first calls to prove
the client reaches the server at all:

```php
use ForgeDashboard\Client;

$client = new Client('https://forge-dashboard.example.com');

$version = $client->health->getVersion();
echo "server version: {$version->getVersion()}\n";
```

The aggregated dashboard needs a token, and demonstrates error handling:

```php
use ForgeDashboard\Client;
use ForgeDashboard\Exception\ApiError;
use ForgeDashboard\Generated\ApiException;

$client = new Client(
    'https://forge-dashboard.example.com',
    apiToken: getenv('FORGE_DASHBOARD_API_TOKEN'),
);

try {
    $dashboard = $client->dashboard->getDashboard();
    foreach ($dashboard->getPullRequests() as $pr) {
        echo "{$pr->getTitle()}: {$pr->getCi()}\n";
    }
} catch (ApiException $exception) {
    $error = ApiError::fromGeneratedException($exception);
    if ($error->statusCode === 401) {
        exit('API token expired or invalid');
    }

    throw $error;
}
```

Every other operation is a direct call on the matching generated Api class —
`$client->settings`, `$client->tokens`, `$client->pullRequests`,
`$client->admin`, `$client->sharing`, `$client->auth`, `$client->webhooks` —
each named after the spec's own tags. These throw the generated
`ForgeDashboard\Generated\ApiException` on any non-2xx response; convert
it to a typed error uniformly with `ApiError::fromGeneratedException()`, as
above.

`ApiError` carries the HTTP status (`statusCode`) and the API's own error
message (`getMessage()`).

The client retries a `429` or `5xx` response with exponential backoff and
jitter (honoring a server-sent `Retry-After`), and never retries any other
`4xx`. Tune it via the constructor's `$maxRetries`/`$retryBaseDelaySeconds`,
or swap the underlying Guzzle handler stack entirely with `$handlerStack`.

## Regenerating the client

See [CONTRIBUTING.md](CONTRIBUTING.md) — the generated code is pinned to a
specific forge-dashboard commit and shouldn't drift from it silently.

## Publishing

This package isn't claimed on [Packagist](https://packagist.org/) yet —
that's a manual step (linking a GitHub webhook to a Packagist account) left
for a maintainer to do once the SDK is ready for general use, not something
done as part of this bootstrap.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for building, testing and the release
process.

## License

[MIT](LICENSE) — a permissive license for the client, independent of
forge-dashboard's own AGPL-3.0.
