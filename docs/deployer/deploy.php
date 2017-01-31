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

task('deploy:set_slack', function () {
    if (!has('host')) {
        set('host', 'host');
    }
    if (!has('stages')) {
        set('stages', ['stage']);
    }
    if (!has('release_path')) {
        set('release_path', 'release_path');
    }

    $git_last_log = run("cd {{current_path}} && {{bin/git}} log --oneline -1")->toString();
    $server_name = get('server')['name'];
    if (has('slack')) {
        $slack = get('slack');
    } else {
        $slack = [];
    }
    $slack['message'] = "`{{host}}`에  *{{stage}}* 배포가 완료되었습니다. (서버 이름: $server_name)\n*Release path*: _{{release_path}}_\n*Latest commit*: _" . $git_last_log . "_";
    set('slack', $slack);
});

desc('Build web-font');
task('deploy:build', 'make -C {{release_path}} build');

desc('Deploy your project');
task('deploy', [
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
    'deploy:set_slack',
    'deploy:unlock',
    'cleanup'
]);
after('deploy', 'success');
after('deploy', 'deploy:slack');
