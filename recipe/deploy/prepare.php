<?php
/* (c) Anton Medvedev <anton@medv.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Deployer;

desc('Preparing server for deploy');
task('deploy:prepare', function () {
    // Check if shell is POSIX-compliant
    try {
        cd(''); // To run command as raw.
        $result = run('echo $0')->toString();
        if ($result == 'stdin: is not a tty') {
            throw new \RuntimeException(
                "Looks like ssh inside another ssh.\n" .
                "Help: http://goo.gl/gsdLt9"
            );
        }
    } catch (\RuntimeException $e) {
        $formatter = Deployer::get()->getHelper('formatter');

        $errorMessage = [
            "Shell on your server is not POSIX-compliant. Please change to sh, bash or similar.",
            "Usually, you can change your shell to bash by running: chsh -s /bin/bash",
        ];
        write($formatter->formatBlock($errorMessage, 'error', true));

        throw $e;
    }

    // Create deploy dir
    if (test('[ ! -d {{deploy_path}} ]')) {
        run('mkdir -p {{deploy_path}}');
    }

    // Check for existing /current directory (not symlink)
    if (test('[ ! -L {{current_path}} ] && [ -d {{current_path}} ]')) {
        throw new \RuntimeException('There already is a directory (not symlink) named "' . get('current_dir') . '" in ' . get('deploy_path') . '. Remove this directory so it can be replaced with a symlink for atomic deployments.');
    }

    // Create metadata .dep dir.
    if (test('[ ! -d {{dep_path}} ]')) {
        run('mkdir {{dep_path}}');
    }

    // Create releases dir.
    if (test('[ ! -d {{releases_path}} ]')) {
        run('mkdir {{releases_path}}');
    }

    // Create shared dir.
    if (test('[ ! -d {{shared_path}} ]')) {
        run('mkdir {{shared_path}}');
    }
});
