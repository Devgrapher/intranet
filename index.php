<?php
use Gnf\NamespaceRouter\NamespaceRouteServiceProvider;
use Intra\Config\Config;
use Intra\Controller\RootController;
use Intra\Core\Application;
use Intra\Model\SessionModel;
use Intra\Service\IntraDb;
use Intra\Service\Ridi;
use Intra\Service\User\UserPolicy;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;

$autoloader = require_once(__DIR__ . "/vendor/autoload.php");
$autoloader->add('Intra', __DIR__ . '/src');

Config::loadIfExist(__DIR__ . '/ConfigDevelop.php');
Config::loadIfExist(__DIR__ . '/ConfigRelease.php');

date_default_timezone_set('Asia/Seoul');

Ridi::enableSentry();
IntraDb::bootDB();
SessionModel::init();

if (Application::run(__DIR__ . "/assets/controls", __DIR__ . "/assets/views")) {
    exit;
}

$app = new Silex\Application;
$app->register(new NamespaceRouteServiceProvider(RootController::class, '/'));
$app->register(new TwigServiceProvider());
$app->before(function (Request $request) {
    return UserPolicy::assertRestrictedPath($request);
});
$app['debug'] = Config::$is_dev;
$app['twig.path'] = [__DIR__ . '/assets/views'];

$app->run();
