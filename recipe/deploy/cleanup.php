<?php
/* (c) Anton Medvedev <anton@medv.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Deployer;

desc('Cleaning up old releases');
task('cleanup', function () {
    $releases = get('releases_list');

    $keep = get('keep_releases');

    if ($keep === -1) {
        // Keep unlimited releases.
        return;
    }

    while ($keep - 1 > 0) {
        array_shift($releases);
        --$keep;
    }

    foreach ($releases as $release) {
        run("rm -rf {{releases_path}}/$release");
    }

    if (test('[ -e {{release_path}} ]') || test('[ -h {{release_path}} ]')) {
        run('rm {{release_path}}');
    }
});
