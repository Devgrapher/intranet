<?php
namespace Deployer;

require_once 'recipe/common.php';
require_once 'vendor/deployer/recipes/slack.php';

require_once __DIR__ . '/task/slackTask.php';
require_once __DIR__ . '/task/vaultTask.php';

// Configuration
set('shared_files', [
    '.env'
]);
set('shared_dirs', [
    'upload'
]);
set('writable_dirs', []);

set('use_relative_symlink', false);
set('default_stage', 'dev');

// Servers
foreach (glob(__DIR__ . '/stage/*.yml') as $filename) {
    inventory($filename);
}

// Tasks
desc('Build web-front');
task('deploy:build', 'make -C {{release_path}} build');

desc('Deploy your project');
task('deploy', [
    'deploy:slack_comment',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:build',
    //'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup'
]);
after('deploy', 'success');

after('deploy', 'deploy:slack_success');
fail('deploy', 'deploy:slack_fail');
