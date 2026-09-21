# ForgeDashboardGenerated

Aggregates every repository the configured GitHub and Forgejo accounts
have write access to into one read-only view: open pull requests (with
CI status), open issues, and per-forge reachability. The backend holds
both forges' credentials; the frontend only ever calls the endpoints
below.


For more information, please visit [https://github.com/alrayyes/forge-dashboard](https://github.com/alrayyes/forge-dashboard).

## Installation & Usage

### Requirements

PHP 8.1 and later.

### Composer

To install the bindings via [Composer](https://getcomposer.org/), add the following to `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/GIT_USER_ID/GIT_REPO_ID.git"
    }
  ],
  "require": {
    "GIT_USER_ID/GIT_REPO_ID": "*@dev"
  }
}
```

Then run `composer install`

### Manual Installation

Download the files and include `autoload.php`:

```php
<?php
require_once('/path/to/ForgeDashboardGenerated/vendor/autoload.php');
```

## Getting Started

Please follow the [installation procedure](#installation--usage) and then run the following:

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



// Configure Bearer authorization: bearerAuth
$config = ForgeDashboard\Generated\Configuration::getDefaultConfiguration()->setAccessToken('YOUR_ACCESS_TOKEN');


$apiInstance = new ForgeDashboard\Generated\Api\AdminApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);
$admin_invite_create_request = new \ForgeDashboard\Generated\Model\AdminInviteCreateRequest(); // \ForgeDashboard\Generated\Model\AdminInviteCreateRequest

try {
    $result = $apiInstance->createInvite($admin_invite_create_request);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AdminApi->createInvite: ', $e->getMessage(), PHP_EOL;
}

```

## API Endpoints

All URIs are relative to *https://localhost:8080*

Class | Method | HTTP request | Description
------------ | ------------- | ------------- | -------------
*AdminApi* | [**createInvite**](docs/Api/AdminApi.md#createinvite) | **POST** /api/admin/invites | Generate a new single-use registration invite
*AdminApi* | [**deleteUser**](docs/Api/AdminApi.md#deleteuser) | **DELETE** /api/admin/users/{username} | Remove a user outright
*AdminApi* | [**listInvites**](docs/Api/AdminApi.md#listinvites) | **GET** /api/admin/invites | List outstanding registration invites
*AdminApi* | [**listUsers**](docs/Api/AdminApi.md#listusers) | **GET** /api/admin/users | List every registered user
*AdminApi* | [**revokeInvite**](docs/Api/AdminApi.md#revokeinvite) | **POST** /api/admin/invites/{token}/revoke | Revoke an outstanding invite
*AdminApi* | [**revokeUser**](docs/Api/AdminApi.md#revokeuser) | **POST** /api/admin/users/{username}/revoke | Revoke a user&#39;s passkeys and sessions
*AuthApi* | [**beginAddCredential**](docs/Api/AuthApi.md#beginaddcredential) | **POST** /api/auth/credentials/begin | Start a ceremony to add another passkey to the signed-in account
*AuthApi* | [**beginLogin**](docs/Api/AuthApi.md#beginlogin) | **POST** /api/auth/login/begin | Start a passkey login ceremony
*AuthApi* | [**beginRegistration**](docs/Api/AuthApi.md#beginregistration) | **POST** /api/auth/register/begin | Start a passkey registration ceremony
*AuthApi* | [**deleteCredential**](docs/Api/AuthApi.md#deletecredential) | **DELETE** /api/auth/credentials/{id} | Remove one of the signed-in user&#39;s own passkeys
*AuthApi* | [**finishAddCredential**](docs/Api/AuthApi.md#finishaddcredential) | **POST** /api/auth/credentials/finish | Complete an add-credential ceremony
*AuthApi* | [**finishLogin**](docs/Api/AuthApi.md#finishlogin) | **POST** /api/auth/login/finish | Complete a passkey login ceremony
*AuthApi* | [**finishRegistration**](docs/Api/AuthApi.md#finishregistration) | **POST** /api/auth/register/finish | Complete a passkey registration ceremony
*AuthApi* | [**getRegistrationStatus**](docs/Api/AuthApi.md#getregistrationstatus) | **GET** /api/auth/registration-status | Whether self-registration is open
*AuthApi* | [**getSession**](docs/Api/AuthApi.md#getsession) | **GET** /api/auth/session | Who, if anyone, the current session cookie belongs to
*AuthApi* | [**listCredentials**](docs/Api/AuthApi.md#listcredentials) | **GET** /api/auth/credentials | List the signed-in user&#39;s own passkeys
*AuthApi* | [**logout**](docs/Api/AuthApi.md#logout) | **POST** /api/auth/logout | End the current session
*DashboardApi* | [**disableAutoUpdateBranch**](docs/Api/DashboardApi.md#disableautoupdatebranch) | **POST** /api/repos/auto-update-branch/disable | Turn off automatic branch updates for one tracked repo
*DashboardApi* | [**enableAutoUpdateBranch**](docs/Api/DashboardApi.md#enableautoupdatebranch) | **POST** /api/repos/auto-update-branch/enable | Turn on automatic branch updates for one tracked repo
*DashboardApi* | [**getDashboard**](docs/Api/DashboardApi.md#getdashboard) | **GET** /api/dashboard | The aggregated view
*DashboardApi* | [**ignoreRepo**](docs/Api/DashboardApi.md#ignorerepo) | **POST** /api/repos/ignore | Hide one tracked repo&#39;s pull requests, issues, or both from the dashboard and Insights
*DashboardApi* | [**refreshDashboard**](docs/Api/DashboardApi.md#refreshdashboard) | **POST** /api/dashboard/refresh | Trigger an immediate refresh of the signed-in user&#39;s own dashboard
*DashboardApi* | [**streamDashboard**](docs/Api/DashboardApi.md#streamdashboard) | **GET** /api/dashboard/stream | Server-Sent Events stream of the signed-in user&#39;s own dashboard
*DashboardApi* | [**unignoreRepo**](docs/Api/DashboardApi.md#unignorerepo) | **POST** /api/repos/unignore | Stop ignoring one tracked repo
*HealthApi* | [**getVersion**](docs/Api/HealthApi.md#getversion) | **GET** /api/version | The running server&#39;s own version
*HealthApi* | [**health**](docs/Api/HealthApi.md#health) | **GET** /healthz | Liveness
*PullRequestsApi* | [**closePullRequest**](docs/Api/PullRequestsApi.md#closepullrequest) | **POST** /api/pull-requests/close | Close one pull request without merging it, on the signed-in user&#39;s behalf
*PullRequestsApi* | [**getPullRequestChecks**](docs/Api/PullRequestsApi.md#getpullrequestchecks) | **GET** /api/pull-requests/checks | List every job/check run against a pull request&#39;s head commit, on demand
*PullRequestsApi* | [**mergePullRequest**](docs/Api/PullRequestsApi.md#mergepullrequest) | **POST** /api/pull-requests/merge | Merge one pull request, on the signed-in user&#39;s behalf
*PullRequestsApi* | [**postPullRequestDependabotAction**](docs/Api/PullRequestsApi.md#postpullrequestdependabotaction) | **POST** /api/pull-requests/dependabot-action | Post one of Dependabot&#39;s own documented PR-comment commands on a pull request, on the signed-in user&#39;s behalf
*PullRequestsApi* | [**postPullRequestRenovateRebase**](docs/Api/PullRequestsApi.md#postpullrequestrenovaterebase) | **POST** /api/pull-requests/renovate-rebase | Trigger Renovate&#39;s own rebase/retry on a pull request, on the signed-in user&#39;s behalf
*PullRequestsApi* | [**updatePullRequestBranch**](docs/Api/PullRequestsApi.md#updatepullrequestbranch) | **POST** /api/pull-requests/update-branch | Bring one pull request&#39;s branch up to date with its base, on the signed-in user&#39;s behalf
*SettingsApi* | [**getBotPrUpdatesSetting**](docs/Api/SettingsApi.md#getbotprupdatessetting) | **GET** /api/settings/bot-pr-updates | Whether bot-managed pull request branches can be updated
*SettingsApi* | [**getFilterState**](docs/Api/SettingsApi.md#getfilterstate) | **GET** /api/settings/filter-state | The signed-in user&#39;s own saved dashboard/Insights filter state
*SettingsApi* | [**getSettings**](docs/Api/SettingsApi.md#getsettings) | **GET** /api/settings | The signed-in user&#39;s own forge configuration
*SettingsApi* | [**getTheme**](docs/Api/SettingsApi.md#gettheme) | **GET** /api/settings/theme | The signed-in user&#39;s own saved theme preference
*SettingsApi* | [**putSettings**](docs/Api/SettingsApi.md#putsettings) | **PUT** /api/settings | Save the signed-in user&#39;s forge configuration
*SettingsApi* | [**setFilterState**](docs/Api/SettingsApi.md#setfilterstate) | **PUT** /api/settings/filter-state | Save the signed-in user&#39;s own dashboard/Insights filter state
*SettingsApi* | [**setTheme**](docs/Api/SettingsApi.md#settheme) | **PUT** /api/settings/theme | Save the signed-in user&#39;s own theme preference
*SharingApi* | [**getSharing**](docs/Api/SharingApi.md#getsharing) | **GET** /api/sharing | The signed-in user&#39;s sharing relationships
*SharingApi* | [**shareWith**](docs/Api/SharingApi.md#sharewith) | **PUT** /api/sharing/{username} | Share the signed-in user&#39;s dashboard with username
*SharingApi* | [**unshareWith**](docs/Api/SharingApi.md#unsharewith) | **DELETE** /api/sharing/{username} | Stop sharing the signed-in user&#39;s dashboard with username
*TokensApi* | [**createAPIToken**](docs/Api/TokensApi.md#createapitoken) | **POST** /api/tokens | Generate a new personal API token
*TokensApi* | [**deleteAPIToken**](docs/Api/TokensApi.md#deleteapitoken) | **DELETE** /api/tokens/{id} | Revoke a personal API token
*TokensApi* | [**listAPITokens**](docs/Api/TokensApi.md#listapitokens) | **GET** /api/tokens | List the signed-in user&#39;s own personal API tokens
*WebhooksApi* | [**ensureWebhook**](docs/Api/WebhooksApi.md#ensurewebhook) | **POST** /api/webhooks/ensure | Create or fix up a webhook on one tracked repo, on the signed-in user&#39;s behalf
*WebhooksApi* | [**receiveForgejoWebhook**](docs/Api/WebhooksApi.md#receiveforgejowebhook) | **POST** /api/webhooks/forgejo/{webhookToken} | Receive a Forgejo repository webhook delivery
*WebhooksApi* | [**receiveGitHubWebhook**](docs/Api/WebhooksApi.md#receivegithubwebhook) | **POST** /api/webhooks/github/{webhookToken} | Receive a GitHub repository webhook delivery

## Models

- [APIToken](docs/Model/APIToken.md)
- [APITokenCreateRequest](docs/Model/APITokenCreateRequest.md)
- [APITokenCreateResponse](docs/Model/APITokenCreateResponse.md)
- [AdminInvite](docs/Model/AdminInvite.md)
- [AdminInviteCreateRequest](docs/Model/AdminInviteCreateRequest.md)
- [AdminInviteCreateResponse](docs/Model/AdminInviteCreateResponse.md)
- [AdminUser](docs/Model/AdminUser.md)
- [BotPrUpdatesResponse](docs/Model/BotPrUpdatesResponse.md)
- [CIStatus](docs/Model/CIStatus.md)
- [Check](docs/Model/Check.md)
- [CheckState](docs/Model/CheckState.md)
- [Credential](docs/Model/Credential.md)
- [Dashboard](docs/Model/Dashboard.md)
- [Error](docs/Model/Error.md)
- [Forge](docs/Model/Forge.md)
- [ForgeErrorKind](docs/Model/ForgeErrorKind.md)
- [ForgeHealth](docs/Model/ForgeHealth.md)
- [Health](docs/Model/Health.md)
- [Issue](docs/Model/Issue.md)
- [Label](docs/Model/Label.md)
- [LoginBeginRequest](docs/Model/LoginBeginRequest.md)
- [MergeStatus](docs/Model/MergeStatus.md)
- [PullRequest](docs/Model/PullRequest.md)
- [PullRequestActionRequest](docs/Model/PullRequestActionRequest.md)
- [PullRequestChecksResponse](docs/Model/PullRequestChecksResponse.md)
- [PullRequestDependabotActionRequest](docs/Model/PullRequestDependabotActionRequest.md)
- [RateLimit](docs/Model/RateLimit.md)
- [RegisterBeginRequest](docs/Model/RegisterBeginRequest.md)
- [RegistrationStatus](docs/Model/RegistrationStatus.md)
- [RepoIgnoreRequest](docs/Model/RepoIgnoreRequest.md)
- [RepoStatus](docs/Model/RepoStatus.md)
- [SessionUser](docs/Model/SessionUser.md)
- [SettingsRequest](docs/Model/SettingsRequest.md)
- [SettingsResponse](docs/Model/SettingsResponse.md)
- [SharedUser](docs/Model/SharedUser.md)
- [SharingResponse](docs/Model/SharingResponse.md)
- [ThemeRequest](docs/Model/ThemeRequest.md)
- [ThemeResponse](docs/Model/ThemeResponse.md)
- [Version](docs/Model/Version.md)
- [WebhookEnsureRequest](docs/Model/WebhookEnsureRequest.md)

## Authorization

Authentication schemes defined for the API:
### bearerAuth

- **Type**: Bearer authentication

## Tests

To run the tests, use:

```bash
composer install
vendor/bin/phpunit
```

## Author



## About this package

This PHP package is automatically generated by the [OpenAPI Generator](https://openapi-generator.tech) project:

- API version: `1.0.0`
    - Package version: `0.1.0`
    - Generator version: `7.25.0`
- Build package: `org.openapitools.codegen.languages.PhpClientCodegen`
