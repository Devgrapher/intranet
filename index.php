<?php
use Intra\Core\IntraApplication;
use Intra\Model\SessionModel;
use Intra\Service\Ridi;

$autoloader = require_once __DIR__ . "/vendor/autoload.php";

if (is_readable(__DIR__ . '/.env')) {
    $dotenv = new Dotenv\Dotenv(__DIR__, '.env');
    $dotenv->overload();
    $dotenv->required(['mysql_host', 'mysql_user', 'mysql_password', 'mysql_db']);
}

date_default_timezone_set('Asia/Seoul');

Ridi::enableSentry();
SessionModel::init();

$app = new IntraApplication([
    'debug' => $_ENV['is_dev'],
    'twig.path' => [__DIR__ . '/assets/views'],
]);
$app->run();
