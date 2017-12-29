<?php
declare(strict_types=1);

use Intra\Service\User\UserPolicy;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

$app->before(function (Request $request) {
    return UserPolicy::assertRestrictedPath($request);
});

$app->get('/', function (Application $app) {
    $subRequest = Request::create('/posts/notice', 'GET');

    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
});

$app->mount('/api', new Intra\Controller\APIController());
$app->mount('/holidays', new Intra\Controller\HolidaysController());
$app->mount('/organization', new Intra\Controller\OrganizationController());
$app->mount('/payments', new Intra\Controller\PaymentsController());
$app->mount('/posts', new Intra\Controller\PostsController());
$app->mount('/press', new Intra\Controller\PressController());
$app->mount('/programs', new Intra\Controller\ProgramsController());
$app->mount('/receipts', new Intra\Controller\ReceiptsController());
$app->mount('/users', new Intra\Controller\UsersController());
$app->mount('/usersession', new Intra\Controller\UserSessionController());
$app->mount('/weekly', new Intra\Controller\WeeklyController());
$app->mount('/flextime', new Intra\Controller\FlexTimeController());
$app->mount('/holidayadmin', new Intra\Controller\HolidayAdminController());
$app->mount('/rooms', new Intra\Controller\RoomsController());
$app->get('/focus/', function (Application $app) {
    $subRequest = Request::create('/rooms/', 'GET', ['type' => 'focus']);

    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
});

$app->mount('/support', new Intra\Controller\SupportController());
$app->mount('/admin', new Intra\Controller\AdminController());
