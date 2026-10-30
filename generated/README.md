# ForgeDashboardGenerated

Aggregates every repository the configured GitHub and Forgejo accounts
have write access to into one read-only view: open pull requests (with
CI status), open issues, and per-forge reachability. The backend holds
both forges' credentials; the frontend only ever calls the endpoints
below.

A request body is capped at 1 MiB, except the forges' own webhook
deliveries (5 MiB). A body whose declared length is over the cap is
answered `413` with an `Error` before it is read; one with no declared
length is cut off at the cap and answered as an invalid body.

## Versioning

The API's major version is in the path: every operation below lives
under `/api/v1`. A breaking change (a removed or renamed field, route
or status, a tighter rule) ships as `/api/v2` beside it, never in place.
`info.version` is this contract's semver. Its major matches the path's,
its minor grows with each compatible addition and its patch with each
correction that changes no behaviour.

Three things stay outside the version because something else holds
their address: `/healthz` and `/readyz` (orchestrators poll them), the
forges' webhook routes under `/api/webhooks/` (each forge keeps the URL
it was registered with), and `GET /api/version`, which says which major
versions the running server speaks.


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


$apiInstance = new ForgeDashboard\Generated\Api\AuthApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client(),
    $config
);

try {
    $result = $apiInstance->beginAddCredential();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling AuthApi->beginAddCredential: ', $e->getMessage(), PHP_EOL;
}

```

## API Endpoints

All URIs are relative to *https://localhost:8080*

Class | Method | HTTP request | Description
------------ | ------------- | ------------- | -------------
*AuthApi* | [**beginAddCredential**](docs/Api/AuthApi.md#beginaddcredential) | **POST** /api/v1/auth/credentials/begin | Start a ceremony to add another passkey to the signed-in account
*AuthApi* | [**beginLogin**](docs/Api/AuthApi.md#beginlogin) | **POST** /api/v1/auth/login/begin | Start a passkey login ceremony
*AuthApi* | [**beginRegistration**](docs/Api/AuthApi.md#beginregistration) | **POST** /api/v1/auth/register/begin | Start a passkey registration ceremony
*AuthApi* | [**deleteCredential**](docs/Api/AuthApi.md#deletecredential) | **DELETE** /api/v1/auth/credentials/{id} | Remove one of the signed-in user&#39;s own passkeys
*AuthApi* | [**finishAddCredential**](docs/Api/AuthApi.md#finishaddcredential) | **POST** /api/v1/auth/credentials/finish | Complete an add-credential ceremony
*AuthApi* | [**finishLogin**](docs/Api/AuthApi.md#finishlogin) | **POST** /api/v1/auth/login/finish | Complete a passkey login ceremony
*AuthApi* | [**finishRegistration**](docs/Api/AuthApi.md#finishregistration) | **POST** /api/v1/auth/register/finish | Complete a passkey registration ceremony
*AuthApi* | [**getRegistrationStatus**](docs/Api/AuthApi.md#getregistrationstatus) | **GET** /api/v1/auth/registration-status | Whether self-registration is open
*AuthApi* | [**getSession**](docs/Api/AuthApi.md#getsession) | **GET** /api/v1/auth/session | Who, if anyone, the current session cookie belongs to
*AuthApi* | [**listCredentials**](docs/Api/AuthApi.md#listcredentials) | **GET** /api/v1/auth/credentials | List the signed-in user&#39;s own passkeys
*AuthApi* | [**logout**](docs/Api/AuthApi.md#logout) | **POST** /api/v1/auth/logout | End the current session
*AuthApi* | [**renameCredential**](docs/Api/AuthApi.md#renamecredential) | **PATCH** /api/v1/auth/credentials/{id} | Rename one of the signed-in user&#39;s own passkeys
*DashboardApi* | [**disableAutoUpdateBranch**](docs/Api/DashboardApi.md#disableautoupdatebranch) | **POST** /api/v1/repos/auto-update-branch/disable | Turn off automatic branch updates for one tracked repo
*DashboardApi* | [**disableRebaseBots**](docs/Api/DashboardApi.md#disablerebasebots) | **POST** /api/v1/repos/rebase-bots/disable | Stop rebasing Dependabot and Renovate pull requests for one tracked repo
*DashboardApi* | [**enableAutoUpdateBranch**](docs/Api/DashboardApi.md#enableautoupdatebranch) | **POST** /api/v1/repos/auto-update-branch/enable | Turn on automatic branch updates for one tracked repo
*DashboardApi* | [**enableRebaseBots**](docs/Api/DashboardApi.md#enablerebasebots) | **POST** /api/v1/repos/rebase-bots/enable | Rebase Dependabot and Renovate pull requests for one tracked repo
*DashboardApi* | [**getCheckLog**](docs/Api/DashboardApi.md#getchecklog) | **GET** /api/v1/checks/log | Read the output of one CI job, on demand
*DashboardApi* | [**getDashboard**](docs/Api/DashboardApi.md#getdashboard) | **GET** /api/v1/dashboard | The aggregated view
*DashboardApi* | [**getForgeApiStats**](docs/Api/DashboardApi.md#getforgeapistats) | **GET** /api/v1/stats/forge-api | How many forge API calls the dashboard has spent, and on what
*DashboardApi* | [**getIssueDetails**](docs/Api/DashboardApi.md#getissuedetails) | **GET** /api/v1/issues/details | Read an issue&#39;s description, comments and reactions, on demand
*DashboardApi* | [**getMainBranches**](docs/Api/DashboardApi.md#getmainbranches) | **GET** /api/v1/main-branches | The CI status of every tracked repository&#39;s default branch
*DashboardApi* | [**getReleaseDetails**](docs/Api/DashboardApi.md#getreleasedetails) | **GET** /api/v1/releases/details | Read a published release&#39;s title and notes, on demand
*DashboardApi* | [**ignoreRepo**](docs/Api/DashboardApi.md#ignorerepo) | **POST** /api/v1/repos/ignore | Hide one tracked repo&#39;s pull requests, issues, or both from the dashboard and Insights
*DashboardApi* | [**listAdvisories**](docs/Api/DashboardApi.md#listadvisories) | **GET** /api/v1/main-branches/advisories | List a repository&#39;s open dependency advisories in full, on demand
*DashboardApi* | [**refreshDashboard**](docs/Api/DashboardApi.md#refreshdashboard) | **POST** /api/v1/dashboard/refresh | Trigger an immediate refresh of the signed-in user&#39;s own dashboard
*DashboardApi* | [**reportDashboardPresence**](docs/Api/DashboardApi.md#reportdashboardpresence) | **POST** /api/v1/presence | Say whether a browser tab of the dashboard is being looked at
*DashboardApi* | [**rerunMainBranchChecks**](docs/Api/DashboardApi.md#rerunmainbranchchecks) | **POST** /api/v1/main-branches/rerun-checks | Rerun only the failed jobs of a repository&#39;s default-branch pipeline, on the signed-in user&#39;s behalf
*DashboardApi* | [**streamDashboard**](docs/Api/DashboardApi.md#streamdashboard) | **GET** /api/v1/dashboard/stream | Server-Sent Events stream of the signed-in user&#39;s own dashboard
*DashboardApi* | [**unignoreRepo**](docs/Api/DashboardApi.md#unignorerepo) | **POST** /api/v1/repos/unignore | Stop ignoring one tracked repo
*DashboardApi* | [**watchDashboardStreamChecks**](docs/Api/DashboardApi.md#watchdashboardstreamchecks) | **POST** /api/v1/dashboard/stream/watch | Choose which job lists a dashboard stream sends
*HealthApi* | [**getVersion**](docs/Api/HealthApi.md#getversion) | **GET** /api/version | The running server&#39;s own version
*HealthApi* | [**health**](docs/Api/HealthApi.md#health) | **GET** /healthz | Liveness
*HealthApi* | [**ready**](docs/Api/HealthApi.md#ready) | **GET** /readyz | Readiness
*IssuesApi* | [**closeIssue**](docs/Api/IssuesApi.md#closeissue) | **POST** /api/v1/issues/close | Close one issue, on the signed-in user&#39;s behalf
*IssuesApi* | [**commentOnIssue**](docs/Api/IssuesApi.md#commentonissue) | **POST** /api/v1/issues/comment | Post a comment on one issue, on the signed-in user&#39;s behalf
*IssuesApi* | [**reopenIssue**](docs/Api/IssuesApi.md#reopenissue) | **POST** /api/v1/issues/reopen | Reopen one issue that was closed, on the signed-in user&#39;s behalf
*PullRequestsApi* | [**cancelPullRequestAutoMerge**](docs/Api/PullRequestsApi.md#cancelpullrequestautomerge) | **POST** /api/v1/pull-requests/auto-merge/cancel | Cancel auto-merge on a pull request, on the signed-in user&#39;s behalf
*PullRequestsApi* | [**cancelPullRequestRenovateRebase**](docs/Api/PullRequestsApi.md#cancelpullrequestrenovaterebase) | **POST** /api/v1/pull-requests/renovate-rebase/cancel | Withdraw a requested Renovate rebase on a pull request, on the signed-in user&#39;s behalf
*PullRequestsApi* | [**cancelWorkflowRun**](docs/Api/PullRequestsApi.md#cancelworkflowrun) | **POST** /api/v1/checks/cancel-run | Cancel one running or queued Actions workflow run, on the signed-in user&#39;s behalf
*PullRequestsApi* | [**closePullRequest**](docs/Api/PullRequestsApi.md#closepullrequest) | **POST** /api/v1/pull-requests/close | Close one pull request without merging it, on the signed-in user&#39;s behalf
*PullRequestsApi* | [**enablePullRequestAutoMerge**](docs/Api/PullRequestsApi.md#enablepullrequestautomerge) | **POST** /api/v1/pull-requests/auto-merge | Arm auto-merge on a pull request, on the signed-in user&#39;s behalf
*PullRequestsApi* | [**getPullRequestDetails**](docs/Api/PullRequestsApi.md#getpullrequestdetails) | **GET** /api/v1/pull-requests/details | Read a pull request&#39;s description, comments and reactions, on demand
*PullRequestsApi* | [**hidePullRequest**](docs/Api/PullRequestsApi.md#hidepullrequest) | **POST** /api/v1/pull-requests/hide | Hide one pull request from the signed-in user&#39;s dashboard
*PullRequestsApi* | [**mergePullRequest**](docs/Api/PullRequestsApi.md#mergepullrequest) | **POST** /api/v1/pull-requests/merge | Merge one pull request, on the signed-in user&#39;s behalf
*PullRequestsApi* | [**postPullRequestDependabotAction**](docs/Api/PullRequestsApi.md#postpullrequestdependabotaction) | **POST** /api/v1/pull-requests/dependabot-action | Post one of Dependabot&#39;s own documented PR-comment commands on a pull request, on the signed-in user&#39;s behalf
*PullRequestsApi* | [**postPullRequestRenovateRebase**](docs/Api/PullRequestsApi.md#postpullrequestrenovaterebase) | **POST** /api/v1/pull-requests/renovate-rebase | Trigger Renovate&#39;s own rebase/retry on a pull request, on the signed-in user&#39;s behalf
*PullRequestsApi* | [**reopenPullRequest**](docs/Api/PullRequestsApi.md#reopenpullrequest) | **POST** /api/v1/pull-requests/reopen | Reopen one pull request that was closed without merging, on the signed-in user&#39;s behalf
*PullRequestsApi* | [**rerunPullRequestChecks**](docs/Api/PullRequestsApi.md#rerunpullrequestchecks) | **POST** /api/v1/pull-requests/rerun-checks | Rerun only the failed jobs of one pull request&#39;s checks, on the signed-in user&#39;s behalf
*PullRequestsApi* | [**setPullRequestAutoUpdate**](docs/Api/PullRequestsApi.md#setpullrequestautoupdate) | **POST** /api/v1/pull-requests/auto-update | Turn the automatic branch update on or off for one pull request
*PullRequestsApi* | [**unhidePullRequest**](docs/Api/PullRequestsApi.md#unhidepullrequest) | **POST** /api/v1/pull-requests/unhide | Bring a hidden pull request back onto the signed-in user&#39;s dashboard
*PullRequestsApi* | [**updatePullRequestBranch**](docs/Api/PullRequestsApi.md#updatepullrequestbranch) | **POST** /api/v1/pull-requests/update-branch | Bring one pull request&#39;s branch up to date with its base, on the signed-in user&#39;s behalf
*QueuedActionsApi* | [**cancelQueuedAction**](docs/Api/QueuedActionsApi.md#cancelqueuedaction) | **DELETE** /api/v1/queued-actions/{id} | Cancel a queued action, or clear a finished one from the list
*QueuedActionsApi* | [**listQueuedActions**](docs/Api/QueuedActionsApi.md#listqueuedactions) | **GET** /api/v1/queued-actions | List the signed-in user&#39;s queued actions, waiting and recently finished
*QueuedActionsApi* | [**queueAction**](docs/Api/QueuedActionsApi.md#queueaction) | **POST** /api/v1/queued-actions | Queue a safe action to run when a rate-limited forge resets
*SettingsApi* | [**getFilterState**](docs/Api/SettingsApi.md#getfilterstate) | **GET** /api/v1/settings/filter-state | The signed-in user&#39;s own saved dashboard/Insights filter state
*SettingsApi* | [**getSettings**](docs/Api/SettingsApi.md#getsettings) | **GET** /api/v1/settings | The signed-in user&#39;s own forge configuration
*SettingsApi* | [**getSettingsSummary**](docs/Api/SettingsApi.md#getsettingssummary) | **GET** /api/v1/settings/summary | The signed-in user&#39;s settings, read without side effects
*SettingsApi* | [**getTheme**](docs/Api/SettingsApi.md#gettheme) | **GET** /api/v1/settings/theme | The signed-in user&#39;s own saved theme preference
*SettingsApi* | [**getTimezone**](docs/Api/SettingsApi.md#gettimezone) | **GET** /api/v1/settings/timezone | The signed-in user&#39;s own saved time zone
*SettingsApi* | [**putSettings**](docs/Api/SettingsApi.md#putsettings) | **PUT** /api/v1/settings | Save the signed-in user&#39;s forge configuration
*SettingsApi* | [**setFilterState**](docs/Api/SettingsApi.md#setfilterstate) | **PUT** /api/v1/settings/filter-state | Save the signed-in user&#39;s own dashboard/Insights filter state
*SettingsApi* | [**setTheme**](docs/Api/SettingsApi.md#settheme) | **PUT** /api/v1/settings/theme | Save the signed-in user&#39;s own theme preference
*SettingsApi* | [**setTimezone**](docs/Api/SettingsApi.md#settimezone) | **PUT** /api/v1/settings/timezone | Save the signed-in user&#39;s own time zone
*TokensApi* | [**createAPIToken**](docs/Api/TokensApi.md#createapitoken) | **POST** /api/v1/tokens | Generate a new personal API token
*TokensApi* | [**deleteAPIToken**](docs/Api/TokensApi.md#deleteapitoken) | **DELETE** /api/v1/tokens/{id} | Revoke a personal API token
*TokensApi* | [**listAPITokens**](docs/Api/TokensApi.md#listapitokens) | **GET** /api/v1/tokens | List the signed-in user&#39;s own personal API tokens
*WebhooksApi* | [**ensureWebhook**](docs/Api/WebhooksApi.md#ensurewebhook) | **POST** /api/v1/webhooks/ensure | Create or fix up a webhook on one tracked repo, on the signed-in user&#39;s behalf
*WebhooksApi* | [**receiveForgejoWebhook**](docs/Api/WebhooksApi.md#receiveforgejowebhook) | **POST** /api/webhooks/forgejo/{webhookToken} | Receive a Forgejo repository webhook delivery
*WebhooksApi* | [**receiveGitHubWebhook**](docs/Api/WebhooksApi.md#receivegithubwebhook) | **POST** /api/webhooks/github/{webhookToken} | Receive a GitHub repository webhook delivery

## Models

- [APIToken](docs/Model/APIToken.md)
- [APITokenCreateRequest](docs/Model/APITokenCreateRequest.md)
- [APITokenCreateResponse](docs/Model/APITokenCreateResponse.md)
- [ActionError](docs/Model/ActionError.md)
- [Advisories](docs/Model/Advisories.md)
- [AdvisoryCVSS](docs/Model/AdvisoryCVSS.md)
- [AdvisoryEPSS](docs/Model/AdvisoryEPSS.md)
- [AdvisoryItem](docs/Model/AdvisoryItem.md)
- [AdvisoryList](docs/Model/AdvisoryList.md)
- [AdvisoryReason](docs/Model/AdvisoryReason.md)
- [AdvisorySeverity](docs/Model/AdvisorySeverity.md)
- [AdvisoryState](docs/Model/AdvisoryState.md)
- [AdvisoryWeakness](docs/Model/AdvisoryWeakness.md)
- [AllowedAction](docs/Model/AllowedAction.md)
- [AllowedActionBlocked](docs/Model/AllowedActionBlocked.md)
- [AutoMergeStatus](docs/Model/AutoMergeStatus.md)
- [AutoMergedPullRequest](docs/Model/AutoMergedPullRequest.md)
- [AutoUpdate](docs/Model/AutoUpdate.md)
- [AutoUpdateUnavailable](docs/Model/AutoUpdateUnavailable.md)
- [BotRequest](docs/Model/BotRequest.md)
- [BranchDeletion](docs/Model/BranchDeletion.md)
- [CIStatus](docs/Model/CIStatus.md)
- [CancelRunRequest](docs/Model/CancelRunRequest.md)
- [Check](docs/Model/Check.md)
- [CheckCounts](docs/Model/CheckCounts.md)
- [CheckLog](docs/Model/CheckLog.md)
- [CheckStatus](docs/Model/CheckStatus.md)
- [CheckSubject](docs/Model/CheckSubject.md)
- [ClosingIssue](docs/Model/ClosingIssue.md)
- [Credential](docs/Model/Credential.md)
- [Dashboard](docs/Model/Dashboard.md)
- [DetailsBody](docs/Model/DetailsBody.md)
- [DetailsComment](docs/Model/DetailsComment.md)
- [Error](docs/Model/Error.md)
- [Forge](docs/Model/Forge.md)
- [ForgeApiBucket](docs/Model/ForgeApiBucket.md)
- [ForgeApiBudget](docs/Model/ForgeApiBudget.md)
- [ForgeApiStats](docs/Model/ForgeApiStats.md)
- [ForgeErrorKind](docs/Model/ForgeErrorKind.md)
- [ForgeHealth](docs/Model/ForgeHealth.md)
- [Health](docs/Model/Health.md)
- [Issue](docs/Model/Issue.md)
- [IssueActionRequest](docs/Model/IssueActionRequest.md)
- [IssueCommentRequest](docs/Model/IssueCommentRequest.md)
- [IssueDetails](docs/Model/IssueDetails.md)
- [Label](docs/Model/Label.md)
- [LinkedPullRequest](docs/Model/LinkedPullRequest.md)
- [MainBranch](docs/Model/MainBranch.md)
- [MainBranchCommit](docs/Model/MainBranchCommit.md)
- [MainBranchRelease](docs/Model/MainBranchRelease.md)
- [MainBranchRerunRequest](docs/Model/MainBranchRerunRequest.md)
- [MainBranchRun](docs/Model/MainBranchRun.md)
- [MainBranchStatus](docs/Model/MainBranchStatus.md)
- [MainBranches](docs/Model/MainBranches.md)
- [MentionedIssue](docs/Model/MentionedIssue.md)
- [MergeStatus](docs/Model/MergeStatus.md)
- [NavCounts](docs/Model/NavCounts.md)
- [NavIssueCount](docs/Model/NavIssueCount.md)
- [NavPullRequestCount](docs/Model/NavPullRequestCount.md)
- [Person](docs/Model/Person.md)
- [PresenceAnswer](docs/Model/PresenceAnswer.md)
- [PresenceReport](docs/Model/PresenceReport.md)
- [PullRequest](docs/Model/PullRequest.md)
- [PullRequestActionRequest](docs/Model/PullRequestActionRequest.md)
- [PullRequestAutoUpdateRequest](docs/Model/PullRequestAutoUpdateRequest.md)
- [PullRequestDependabotActionRequest](docs/Model/PullRequestDependabotActionRequest.md)
- [PullRequestDetails](docs/Model/PullRequestDetails.md)
- [PullRequestHideRequest](docs/Model/PullRequestHideRequest.md)
- [PullRequestMergeRequest](docs/Model/PullRequestMergeRequest.md)
- [PullRequestMergeResult](docs/Model/PullRequestMergeResult.md)
- [PullRequestStatus](docs/Model/PullRequestStatus.md)
- [QueuedAction](docs/Model/QueuedAction.md)
- [QueuedActionRequest](docs/Model/QueuedActionRequest.md)
- [RateLimit](docs/Model/RateLimit.md)
- [Reaction](docs/Model/Reaction.md)
- [Reactions](docs/Model/Reactions.md)
- [Ready](docs/Model/Ready.md)
- [RegistrationStatus](docs/Model/RegistrationStatus.md)
- [ReleaseDetails](docs/Model/ReleaseDetails.md)
- [RenameCredentialRequest](docs/Model/RenameCredentialRequest.md)
- [RepoIgnoreRequest](docs/Model/RepoIgnoreRequest.md)
- [RepoStatus](docs/Model/RepoStatus.md)
- [ReviewState](docs/Model/ReviewState.md)
- [RunEvent](docs/Model/RunEvent.md)
- [SessionUser](docs/Model/SessionUser.md)
- [SettingsRequest](docs/Model/SettingsRequest.md)
- [SettingsResponse](docs/Model/SettingsResponse.md)
- [SettingsSummary](docs/Model/SettingsSummary.md)
- [StackPosition](docs/Model/StackPosition.md)
- [StackRef](docs/Model/StackRef.md)
- [StreamDashboard200Response](docs/Model/StreamDashboard200Response.md)
- [StreamWatchRequest](docs/Model/StreamWatchRequest.md)
- [ThemeRequest](docs/Model/ThemeRequest.md)
- [ThemeResponse](docs/Model/ThemeResponse.md)
- [TimezoneRequest](docs/Model/TimezoneRequest.md)
- [TimezoneResponse](docs/Model/TimezoneResponse.md)
- [UpdateRequest](docs/Model/UpdateRequest.md)
- [Version](docs/Model/Version.md)
- [WebhookEnsureRequest](docs/Model/WebhookEnsureRequest.md)

## Authorization

Authentication schemes defined for the API:
### bearerAuth

- **Type**: Bearer authentication

### cookieAuth

- **Type**: API key
- **API key parameter name**: forge_dashboard_session
- **Location**: 


## Tests

To run the tests, use:

```bash
composer install
vendor/bin/phpunit
```

## Author



## About this package

This PHP package is automatically generated by the [OpenAPI Generator](https://openapi-generator.tech) project:

- API version: `2.0.0`
    - Package version: `0.1.0`
    - Generator version: `7.25.0`
- Build package: `org.openapitools.codegen.languages.PhpClientCodegen`
