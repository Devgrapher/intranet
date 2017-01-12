<?php
namespace Deployer;

require 'recipe/common.php';
require 'vendor/deployer/recipes/slack.php';

// Configuration

set('shared_files', [
    'ConfigDevelop.php',
    'ConfigRelease.php'
]);
set('shared_dirs', [
    'upload'
]);
set('writable_dirs', []);

set('use_relative_symlink', false);
set('default_stage', 'dev');


// Servers

foreach (glob(__DIR__ . '/stage/*.yml') as $filename) {
    serverList($filename);
}


// Tasks

task('test', function () {
    writeln("deploy_path: " . get('deploy_path'));
    writeln("current_path: " . get("current_path"));
    writeln(run("cd {{current_path}} && {{bin/git}} show -s")->toString());
});

task('deploy:git_fetch', function () {
    run("cd {{current_path}} && {{bin/git}} reset --hard origin/master");
    run("cd {{current_path}} && {{bin/git}} fetch --all");
});


desc('Update');
task('git_fetch', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:git_fetch',
    'deploy:unlock',
    'cleanup'
]);
after('git_fetch', 'success');

desc('Installing bower components');
task('deploy:bower', function () {
    run('cd {{release_path}}/assets && bower update');
});

desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:bower',
    //'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup'
]);
after('deploy', 'success');
after('deploy', 'deploy:slack');
