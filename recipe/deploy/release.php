<?php
/* (c) Anton Medvedev <anton@medv.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Deployer;

use Deployer\Type\Csv;

set('release_name', function () {
    $list = get('releases_list');

    // Filter out anything that does not look like a release.
    $list = array_filter($list, function ($release) {
        return preg_match('/^[\d\.]+$/', $release);
    });

    $nextReleaseNumber = 1;
    if (count($list) > 0) {
        $nextReleaseNumber = (int)max($list) + 1;
    }

    return (string)$nextReleaseNumber;
}); // name of folder in releases

/**
 * Return list of releases on server.
 */
set('releases_list', function () {
    // If there is no releases return empty list.
    if (!test('[ -d {{releases_path}} ] && [ "$(ls -A {{releases_path}})" ]')) {
        return [];
    }

    // Will list only dirs in releases.
    $list = run('cd {{releases_path}} && ls -t -d */')->toArray();

    // Prepare list.
    $list = array_map(function ($release) {
        return basename(rtrim($release, '/'));
    }, $list);

    $releases = []; // Releases list.

    // Collect releases based on .dep/releases info.
    // Other will be ignored.

    if (test('[ -f {{dep_path}}/releases ]')) {
        $keepReleases = get('keep_releases');
        if ($keepReleases === -1) {
            $csv = run('cat {{dep_path}}/releases');
        } else {
            $csv = run("tail -n " . ($keepReleases + 5) . " {{dep_path}}/releases");
        }

        $metainfo = Csv::parse($csv);

        for ($i = count($metainfo) - 1; $i >= 0; --$i) {
            if (is_array($metainfo[$i]) && count($metainfo[$i]) >= 2) {
                list($date, $release) = $metainfo[$i];
                $index = array_search($release, $list, true);
                if ($index !== false) {
                    $releases[] = $release;
                    unset($list[$index]);
                }
            }
        }
    }

    return $releases;
});

desc('Prepare release');
task('deploy:release', function () {
    // Clean up if there is unfinished release.
    if (test('[ -h {{release_path}} ]')) {
        run('rm -rf "$(readlink {{release_path}})"'); // Delete release.
        run('rm {{release_path}}'); // Delete symlink.
    }

    $releaseName = get('release_name');

    // Fix collisions.
    $i = 0;
    while (test("[ -d {{releases_path}}/{$releaseName} ]")) {
        $releaseName .= '.' . ++$i;
        set('release_name', $releaseName);
    }

    // Metainfo.
    $date = run('date +"%Y%m%d%H%M%S"');

    // Save metainfo about release.
    run("echo '$date,{{release_name}}' >> {{dep_path}}/releases");

    // Make new release.
    run("mkdir {{releases_path}}/{{release_name}}");
    run("{{bin/symlink}} {{releases_path}}/{{release_name}} {{release_path}}");
});
