<?php

declare(strict_types=1);

// The three auto-merge workflows (release, regenerate, Dependabot) ran
// `gh pr merge --auto --squash` and nothing else. When one ran while only a
// non-required check (coverage, docs) was still going, GitHub reports the
// pull request as `unstable` and refuses to arm auto-merge, so the job failed
// and the pull request sat there until someone re-ran it. Each workflow's
// merge step is run here against a fake `gh`, so the decision is tested
// without GitHub.

const UNSTABLE = 'GraphQL: Pull request Pull request is in unstable status (enablePullRequestAutoMerge)';
const CLEAN = 'GraphQL: Pull request Pull request is in clean status (enablePullRequestAutoMerge)';
const CONFLICT = 'X Pull request alrayyes/forge-dashboard-sdk-php#1 is not mergeable: the merge commit cannot be cleanly created.';
const PR = 'https://github.com/alrayyes/forge-dashboard-sdk-php/pull/1';

/** The `run` script of the one step that calls `gh pr merge --auto`. */
function mergeStepScript(string $workflow): string
{
    $lines = explode("\n", (string) file_get_contents(__DIR__.'/../.github/workflows/'.$workflow));
    $script = [];
    $indent = null;
    foreach ($lines as $line) {
        if ($indent === null) {
            if (preg_match('/^(\s*)(?:- )?run: \|\s*$/', $line, $m) === 1) {
                $indent = strlen($m[1]) + 2;
                $script = [];
            }

            continue;
        }
        if (trim($line) === '' || strlen($line) - strlen(ltrim($line)) >= $indent) {
            $script[] = substr($line, min($indent, strlen($line)));

            continue;
        }
        if (str_contains(implode("\n", $script), 'gh pr merge --auto')) {
            break;
        }
        $indent = null;
    }
    expect(implode("\n", $script))->toContain('gh pr merge --auto');

    return implode("\n", $script);
}

/**
 * @param  array{auto: array{string, int}, checks: array{string, int}, direct: array{string, int}}  $answers
 * @return array{int, string, list<string>}
 */
function runMergeStep(string $workflow, array $answers): array
{
    $dir = sys_get_temp_dir().'/auto-merge-'.bin2hex(random_bytes(4));
    mkdir($dir);
    $log = $dir.'/calls.log';
    file_put_contents($log, '');
    // A fake `gh` that logs its arguments and answers per subcommand.
    file_put_contents($dir.'/gh', <<<'SH'
        #!/usr/bin/env bash
        echo "$*" >> "$FAKE_LOG"
        case "$*" in
          *--auto*) printf '%s\n' "$FAKE_AUTO_OUT"; exit "$FAKE_AUTO_RC" ;;
          "pr checks"*) printf '%s\n' "$FAKE_CHECKS_OUT"; exit "$FAKE_CHECKS_RC" ;;
          *) printf '%s\n' "$FAKE_DIRECT_OUT"; exit "$FAKE_DIRECT_RC" ;;
        esac
        SH);
    chmod($dir.'/gh', 0o755);
    file_put_contents($dir.'/step.sh', mergeStepScript($workflow));

    $env = [
        'PATH' => $dir.':'.getenv('PATH'),
        'PR_URL' => PR,
        'GH_TOKEN' => 'secret-token',
        'FAKE_LOG' => $log,
        'FAKE_AUTO_OUT' => $answers['auto'][0],
        'FAKE_AUTO_RC' => (string) $answers['auto'][1],
        'FAKE_CHECKS_OUT' => $answers['checks'][0],
        'FAKE_CHECKS_RC' => (string) $answers['checks'][1],
        'FAKE_DIRECT_OUT' => $answers['direct'][0],
        'FAKE_DIRECT_RC' => (string) $answers['direct'][1],
    ];
    $process = proc_open(['bash', $dir.'/step.sh'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, $env);
    if (! is_resource($process)) {
        throw new RuntimeException('could not start bash');
    }
    $output = stream_get_contents($pipes[1]).stream_get_contents($pipes[2]);
    $code = proc_close($process);
    $calls = array_values(array_filter(explode("\n", (string) file_get_contents($log))));

    return [$code, $output, $calls];
}

$ok = ['', 0];
$workflows = ['release-auto-merge.yml', 'regenerate-auto-merge.yml', 'dependabot-auto-merge.yml'];

foreach ($workflows as $workflow) {
    describe($workflow, function () use ($workflow, $ok): void {
        it('arms auto-merge and stops when GitHub accepts it', function () use ($workflow, $ok): void {
            [$code, , $calls] = runMergeStep($workflow, ['auto' => $ok, 'checks' => $ok, 'direct' => $ok]);

            expect($code)->toBe(0);
            expect($calls)->toBe(['pr merge --auto --squash '.PR]);
        });

        it('merges directly once the required checks passed when GitHub says unstable', function () use ($workflow, $ok): void {
            [$code, , $calls] = runMergeStep($workflow, ['auto' => [UNSTABLE, 1], 'checks' => $ok, 'direct' => $ok]);

            expect($code)->toBe(0);
            expect($calls)->toBe([
                'pr merge --auto --squash '.PR,
                'pr checks '.PR.' --required',
                'pr merge --squash '.PR,
            ]);
        });

        it('merges directly once the required checks passed when the pull request is already clean', function () use ($workflow, $ok): void {
            [$code, , $calls] = runMergeStep($workflow, ['auto' => [CLEAN, 1], 'checks' => $ok, 'direct' => $ok]);

            expect($code)->toBe(0);
            expect($calls)->toHaveCount(3);
        });

        it('merges nothing while a required check is pending or failing', function () use ($workflow, $ok): void {
            // `gh pr checks --required` exits 8 while pending and 1 on a failure.
            // The branch protection has no enforce_admins, so GitHub itself would
            // let this token merge: the step has to hold the line.
            foreach ([8, 1] as $rc) {
                [$code, $output, $calls] = runMergeStep($workflow, [
                    'auto' => [UNSTABLE, 1],
                    'checks' => ['test pending', $rc],
                    'direct' => $ok,
                ]);

                expect($code)->not->toBe(0);
                expect($calls)->toBe(['pr merge --auto --squash '.PR, 'pr checks '.PR.' --required']);
                expect($output)->toContain('test pending');
            }
        });

        it('fails visibly, and merges nothing, on any other refusal', function () use ($workflow, $ok): void {
            [$code, $output, $calls] = runMergeStep($workflow, ['auto' => [CONFLICT, 1], 'checks' => $ok, 'direct' => $ok]);

            expect($code)->not->toBe(0);
            expect($calls)->toHaveCount(1);
            expect($output)->toContain('cannot be cleanly created');
        });

        it('fails when the direct merge is refused', function () use ($workflow, $ok): void {
            [$code, , $calls] = runMergeStep($workflow, ['auto' => [UNSTABLE, 1], 'checks' => $ok, 'direct' => ['refused', 1]]);

            expect($code)->not->toBe(0);
            expect($calls)->toHaveCount(3);
        });

        it('does not print the token', function () use ($workflow, $ok): void {
            [, $output] = runMergeStep($workflow, ['auto' => [UNSTABLE, 1], 'checks' => $ok, 'direct' => $ok]);

            expect($output)->not->toContain('secret-token');
        });
    });
}
