<?php
namespace Deployer;

desc('Upload .env');
task('env:push', function () {
    if (!has('deploy_path')) {
        set('deploy_path', 'deploy_path');
    }

    $server_name = get('server')['name'];
    upload(".env_$server_name", "{{deploy_path}}/shared/.env.upload");

    // replace file
    run("cp -f {{deploy_path}}/shared/.env {{deploy_path}}/shared/.env.bak");
    run("mv -f {{deploy_path}}/shared/.env.upload {{deploy_path}}/shared/.env");

    runLocally("rm .env_$server_name");
});

desc('Download .env');
task('env:pull', function () {
    if (!has('deploy_path')) {
        set('deploy_path', 'deploy_path');
    }

    $server_name = get('server')['name'];
    download(".env_$server_name", "{{deploy_path}}/shared/.env");
});

desc('Rollback .env with .env.bak');
task('env:rollback', function () {
    if (!has('deploy_path')) {
        set('deploy_path', 'deploy_path');
    }

    run("cp -f {{deploy_path}}/shared/.env.bak {{deploy_path}}/shared/.env");
});
