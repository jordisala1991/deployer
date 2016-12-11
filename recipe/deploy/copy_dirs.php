<?php
/* (c) Anton Medvedev <anton@medv.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Deployer;

desc('Copy directories');
task('deploy:copy_dirs', function () {
    $dirs = get('copy_dirs');

    foreach ($dirs as $dir) {
        // Delete directory if exists.
        if (test("[ -d $(echo {{release_path}}/{$dir}) ]")) {
            run("rm -rf {{release_path}}/{$dir}");
        }

        // Copy directory.
        if (test("[ -d $(echo {{current_path}}/{$dir}) ]")) {
            run("cp -rpf {{current_path}}/$dir {{release_path}}/$dir");
        }
    }
});
