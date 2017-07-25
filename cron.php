<?php
use Intra\Service\Cron\CronMaster;
use Intra\Service\IntraDb;
use Intra\Service\Ridi;

$autoloader = require_once __DIR__ . "/vendor/autoload.php";
$autoloader->add('Intra', __DIR__ . '/src');

if (is_readable(__DIR__ . '/.env')) {
    $dotenv = new Dotenv\Dotenv(__DIR__, '.env');
    $dotenv->load();
    $dotenv->required(['mysql_host', 'mysql_user', 'mysql_password', 'mysql_db']);
}

date_default_timezone_set('Asia/Seoul');

Ridi::enableSentry();
IntraDb::bootDB();

CronMaster::run();
