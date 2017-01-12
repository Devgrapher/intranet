<?php

namespace Intra\Config;

use Intra\Core\ConfigLoader;

class Config
{
    use ConfigLoader;

    public static $upload_dir;

    public static $mysql_host;
    public static $mysql_user;
    public static $mysql_password;
    public static $mysql_db;

    public static $sentry_key;
    public static $sentry_public_key;

    public static $domain = "ridi.com";

    public static $is_dev = false;
    public static $test_mails = [];

    public static $azure = [
        'clientId' => [],
        'password' => [],
        'redirectURI' => [],
        'resourceURI' => [],
        'appTenantDomainName' => [],
        'apiVersion' => []
    ];

    public static $recipients = [
        'payment' => [],
        'payment_admin' => [],
        'holiday' => []
    ];

    public static $user_policy = [
        'first_page_editable' => [],
        'holiday_editable' => [],
        'press_manager' => [],
        'user_manager' => [],
        'post_admin' => [],
        'payment_admin' => [],
        'receipts_admin' => [],
		'support_admin' => [],
    ];

    public static $supports = [
        'mails' => [
            'all' => [],
            'device' => [],
            'family_event' => [],
            'business_card' => [],
            'depot' => [],
            'gift_card' => [],
        ],
    ];

    public static $ridi_ips = [];

    public static $mailgun_api_key;
    public static $mailgun_from;
}
