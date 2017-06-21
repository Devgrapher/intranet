<?php
namespace Deployer;

desc('set vault config');
task('deploy:set_vault', function () {
    if (has('vault')) {
        $vault = get('vault');
    } else {
        $vault = [];
    }

    if (empty($vault['address'])) {
        $vault['address'] = ask("vault address:");
    }

    if (empty($vault['id'])) {
        $vault['id'] = ask("vault id:");
    }

    if (empty($vault['pw'])) {
        $vault['pw'] = askHiddenResponse("vault pw:");
    }

    set('vault', $vault);
});

desc('start consul-template');
task('deploy:consul-template', function () {
    if (!has('vault')) {
        writeln('no vault config. skip consul-template..');
        return;
    }

    $vault = get('vault');
    $vault['consul_template_config'] = '/Users/imkkt/deploy-test/current/docs/consul-template/config.hcl';
    $login_res = run("curl ${vault['address']}/v1/auth/userpass/login/${vault['id']} -d '{ \"password\": \"${vault['pw']}\" }'");
    $token = json_decode($login_res, true)['auth']['client_token'];
    run("VAULT_TOKEN=$token nohup consul-template -config ${vault['consul_template_config']} -vault-addr \"${vault['address']}\" &");
});
