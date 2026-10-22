<?php

declare(strict_types=1);

// scripts/generate.sh only adds and overwrites files, so an operation that left the spec
// used to linger in generated/. It has to clear the directory before generating.

function generateWorkDir(): string
{
    $dir = sys_get_temp_dir().'/generate-'.bin2hex(random_bytes(4));
    mkdir($dir.'/scripts', 0o777, true);
    mkdir($dir.'/bin', 0o777, true);
    mkdir($dir.'/generated/src/Api', 0o777, true);
    mkdir($dir.'/openapi', 0o777, true);
    copy(__DIR__.'/../scripts/generate.sh', $dir.'/scripts/generate.sh');
    file_put_contents($dir.'/generated/src/Api/GoneApi.php', '<?php // stale');
    file_put_contents($dir.'/openapi/openapi.yaml', 'openapi: 3.0.0');
    // A stand-in generator that writes only what the current spec still has.
    file_put_contents($dir.'/bin/docker', "#!/bin/sh\nmkdir -p generated/src/Api\ntouch generated/src/Api/KeptApi.php\n");
    chmod($dir.'/bin/docker', 0o755);

    return $dir;
}

it('drops generated files whose operation left the spec', function (): void {
    $dir = generateWorkDir();

    exec('cd '.escapeshellarg($dir).' && HOME='.escapeshellarg($dir).' PATH='.escapeshellarg($dir.'/bin').':$PATH bash scripts/generate.sh 2>&1', $output, $status);

    expect($status)->toBe(0);
    expect(file_exists($dir.'/generated/src/Api/GoneApi.php'))->toBeFalse();
    expect(file_exists($dir.'/generated/src/Api/KeptApi.php'))->toBeTrue();
});
it('has no code for the admin and sharing operations the server dropped', function (): void {
    $root = dirname(__DIR__);
    $hits = [];

    foreach (['generated', 'src'] as $dir) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$dir, FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if (! $file instanceof SplFileInfo) {
                continue;
            }

            if (preg_match('/createInvite|listUsers|shareWith/', (string) file_get_contents($file->getPathname())) === 1) {
                $hits[] = $file->getPathname();
            }
        }
    }

    expect($hits)->toBe([]);
});
