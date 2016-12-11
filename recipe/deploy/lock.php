<?php
/* (c) Anton Medvedev <anton@medv.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Deployer;

desc('Lock deploy');
task('deploy:lock', function () {
    if (test('[ -f {{dep_path}}/deploy.lock ]')) {
        throw new \RuntimeException(
            "Deploy locked.\n" .
            "Run deploy:unlock command to unlock."
        );
    }

    run('touch {{dep_path}}/deploy.lock');
});

desc('Unlock deploy');
task('deploy:unlock', function () {
    run('rm {{dep_path}}/deploy.lock');
});
