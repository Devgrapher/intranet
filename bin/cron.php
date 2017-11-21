<?php
use Intra\Service\Cron\CronMaster;
use Intra\Service\IntraDb;
use Intra\Service\Ridi;

$autoloader = require_once __DIR__ . "/../vendor/autoload.php";

if (is_readable(__DIR__ . '/../.env')) {
    $dotenv = new Dotenv\Dotenv(__DIR__ . '/../', '.env');
    $dotenv->overload();
    $dotenv->required(['INTRA_DBHOST', 'INTRA_DBUSER', 'INTRA_DBPASS', 'INTRA_DBNAME']);
}

date_default_timezone_set('Asia/Seoul');

Ridi::enableSentry();
IntraDb::bootDB();

CronMaster::run();
