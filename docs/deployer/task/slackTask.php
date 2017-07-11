<?php
namespace Deployer;

desc('Set slack message comment');
task('deploy:slack_comment', function () {
    if (isQuiet()) {
        return;
    }

    $comment = ask("변경사항 코멘트:");
    on(Deployer::get()->hosts, function ($host) use ($comment) {
        $slack = $host->has('slack') ? $host->get('slack') : [];
        $slack['comment'] = $comment;
        $host->set('slack', $slack);
    });
})->local();

desc('Send success to slack');
task('deploy:slack_success', [
    'deploy:slack_prepare',
    'deploy:slack',
]);

desc('Send fail to slack');
task('deploy:slack_fail', [
    'deploy:slack_prepare',
    'deploy:set_slack_fail',
    'deploy:slack',
]);

desc('Set basic slack config');
task('deploy:slack_prepare', function () {
    $user = trim(runLocally('git config --get user.name'));
    $branch = get('branch');
    $host = get('hostname');
    $stage = has('stage') ? get('stage') : 'local';
    $git_last_log = run("cd {{current_path}} && {{bin/git}} log --oneline -1")->toString();

    $fields = [
        ['title' => 'User', 'value' => $user, 'short' => true],
        ['title' => 'Branch', 'value' => $branch, 'short' => true],
        ['title' => 'Host', 'value' => $host, 'short' => true],
        ['title' => 'Stage', 'value' => $stage, 'short' => true],
    ];

    $slack = has('slack') ? get('slack') : [];
    if (!empty($slack['comment'])) {
        array_push($fields, ['title' => 'Comment', 'value' => $slack['comment'], 'short' => true]);
    }

    $attachment = [
        [
            'color' => '#7CD197',
            'fallback' => "${host}에 ${stage} 배포가 완료되었습니다.",
            'title' => "배포가 완료되었습니다.",
            'text' => "Revision ${git_last_log}",
            'fields' => $fields,
        ],
    ];

    $slack['attachments'] = $attachment;
    set('slack', $slack);
});

desc('Set failed slack config');
task('deploy:set_slack_fail', function () {
    $slack = get('slack');
    $host = get('hostname');
    $stage = has('stage') ? get('stage') : 'local';
    $attachment = $slack['attachments'];

    $attachment['color'] = '#F35A00';
    $attachment['fallback'] = "${host}에 ${stage} 배포가 실패했습니다.";
    $attachment['title'] = "배포가 실패했습니다.";

    set('slack', $slack);
});

desc('Notifying Slack channel of deployment');
task('deploy:slack', function () {
    if (true === get('slack_skip_notification')) {
        return;
    }

    global $php_errormsg;

    $user = trim(runLocally('git config --get user.name'));
    $revision = trim(runLocally('git log -n 1 --format="%h"'));
    $stage = '';
    $branch = get('branch');
    if (input()->hasOption('branch')) {
        $inputBranch = input()->getOption('branch');
        if (!empty($inputBranch)) {
            $branch = $inputBranch;
        }
    }
    $defaultConfig = [
        'channel' => '#general',
        'icon' => ':sunny:',
        'username' => 'Deploy',
        'message' => "Deployment to `{{host}}` on *{{stage}}* was successful\n({{release_path}})",
        'app' => 'app-name',
        'unset_text' => true,
        'attachments' => [
            [
                'text' => sprintf(
                    'Revision %s deployed to %s by %s',
                    substr($revision, 0, 6),
                    $stage,
                    $user
                ),
                'title' => 'Deployment Complete',
                'fallback' => sprintf('Deployment to %s complete.', $stage),
                'color' => '#7CD197',
                'fields' => [
                    [
                        'title' => 'User',
                        'value' => $user,
                        'short' => true,
                    ],
                    [
                        'title' => 'Stage',
                        'value' => $stage,
                        'short' => true,
                    ],
                    [
                        'title' => 'Branch',
                        'value' => $branch,
                        'short' => true,
                    ],
                    [
                        'title' => 'Host',
                        'value' => get('hostname'),
                        'short' => true,
                    ],
                ],
            ],
        ],
    ];

    $newConfig = get('slack');

    if (is_callable($newConfig)) {
        $newConfig = $newConfig();
    }

    $config = array_merge($defaultConfig, (array) $newConfig);

    if (!is_array($config) || !isset($config['token']) || !isset($config['team']) || !isset($config['channel'])) {
        throw new \RuntimeException("Please configure new slack: set('slack', ['token' => 'xoxp...', 'team' => 'team', 'channel' => '#channel', 'messsage' => 'message to send']);");
    }

//    $server = \Deployer\Task\Context::get()->getHost();
//    if ($server instanceof \Deployer\Host\Local) {
//        $user = get('local_user');
//    } else {
//        $user = $server->getConfiguration()->getUser() ?: null;
//    }

    $messagePlaceHolders = [
        '{{release_path}}' => get('release_path'),
        '{{host}}' => get('hostname'),
        '{{stage}}' => $stage,
        '{{user}}' => $user,
        '{{branch}}' => $branch,
        '{{app_name}}' => isset($config['app']) ? $config['app'] : 'app-name',
    ];
    $config['message'] = strtr($config['message'], $messagePlaceHolders);

    $urlParams = [
        'channel' => $config['channel'],
        'token' => $config['token'],
        'text' => $config['message'],
        'username' => $config['username'],
        'icon_emoji' => $config['icon'],
        'pretty' => true,
    ];

    foreach (['unset_text' => 'text', 'icon_url' => 'icon_emoji'] as $set => $unset) {
        if (isset($config[$set])) {
            unset($urlParams[$unset]);
        }
    }

    foreach (['parse', 'link_names', 'icon_url', 'unfurl_links', 'unfurl_media', 'as_user'] as $option) {
        if (isset($config[$option])) {
            $urlParams[$option] = $config[$option];
        }
    }

    if (isset($config['attachments'])) {
        $urlParams['attachments'] = json_encode($config['attachments']);
    }

    $url = 'https://slack.com/api/chat.postMessage?' . http_build_query($urlParams);
    $result = @file_get_contents($url);

    if (!$result) {
        throw new \RuntimeException($php_errormsg);
    }

    $response = @json_decode($result);

    if (!$response || isset($response->error)) {
        throw new \RuntimeException($response->error);
    }
})->desc('Notifying Slack channel of deployment');
