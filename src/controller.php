<?php
declare(strict_types=1);

use Graze\Silex\ControllerProvider\TrailingSlashControllerProvider;
use Intra\Service\User\UserPolicy;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

$app->before(function (Request $request) {
    return UserPolicy::assertRestrictedPath($request);
});

$app->before(function (Request $request) {
    if ($request->getContentType() === 'json') {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : []);
    }
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
$app->mount('/rooms', new Intra\Controller\RoomsController());
$app->mount('/support', new Intra\Controller\SupportController());
$app->get('/focus/', function (Application $app) {
    $subRequest = Request::create('/rooms/', 'GET', ['type' => 'focus']);

    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
});

$app->mount('/admin', function (ControllerCollection $admin) use ($app) {
    $admin->mount('/policy', (new Intra\Controller\Admin\PolicyController())->connect($app));
    $admin->mount('/recipient', (new Intra\Controller\Admin\RecipientController())->connect($app));
    $admin->mount('/user', (new Intra\Controller\Admin\UserController())->connect($app));
    $admin->mount('/holiday', (new Intra\Controller\Admin\HolidayController())->connect($app));
    $admin->mount('/room', (new Intra\Controller\Admin\RoomController())->connect($app));
    $admin->mount('/event_group', (new Intra\Controller\Admin\EventGroupController())->connect($app));
    $admin->mount('/press', (new Intra\Controller\Admin\PressController())->connect($app));
    $admin->mount('/payment', (new Intra\Controller\Admin\PaymentController())->connect($app));
});

$trailingSlashControllerProvider = new TrailingSlashControllerProvider();
$app->register($trailingSlashControllerProvider);
$app->mount('/', $trailingSlashControllerProvider);
