<?php

use Intra\Model\SessionModel;
use Intra\Service\Ridi;
use Symfony\Component\HttpFoundation\Request;

date_default_timezone_set('Asia/Seoul');

$autoloader = require_once __DIR__ . "/../vendor/autoload.php";

if (is_readable(__DIR__ . '/../.env')) {
    $dotenv = new Dotenv\Dotenv(__DIR__ . '/../', '.env');
    $dotenv->overload();
    $dotenv->required(['INTRA_DBHOST', 'INTRA_DBUSER', 'INTRA_DBPASS', 'INTRA_DBNAME']);
}

if (getenv('INTRA_TRUSTED_PROXIES', true) !== false) {
    $trusted_proxies = explode('|', $_ENV['INTRA_TRUSTED_PROXIES']);
    Request::setTrustedProxies($trusted_proxies);

    // This is for avoiding ConflictingHeadersException.
    // See https://github.com/symfony/symfony/issues/20215#issuecomment-261252378
    Request::setTrustedHeaderName(Request::HEADER_FORWARDED, null);
}

/** @var \Silex\Application $app */
$app = require_once __DIR__ . '/../src/app.php';

$app->before(function () {
    Ridi::enableSentry();
    \Intra\Service\IntraDb::bootDB();
    SessionModel::init();
});

require_once __DIR__ . '/../src/controller.php';

$app->run();
