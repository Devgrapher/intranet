<?php
namespace Intra\Core;

use Intra\Controller\APIController;
use Intra\Controller\FlexTimeController;
use Intra\Controller\HolidayAdminController;
use Intra\Controller\HolidaysController;
use Intra\Controller\OrganizationController;
use Intra\Controller\PaymentsController;
use Intra\Controller\PostsController;
use Intra\Controller\PressController;
use Intra\Controller\ProgramsController;
use Intra\Controller\ReceiptsController;
use Intra\Controller\RoomsController;
use Intra\Controller\SupportController;
use Intra\Controller\UsersController;
use Intra\Controller\UserSessionController;
use Intra\Controller\WeeklyController;
use Intra\Service\Menu\MenuService;
use Intra\Service\User\UserPolicy;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class IntraApplication extends Application
{
    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $this->register(new TwigServiceProvider(), [
            'twig.path' => $values['twig.path']
        ]);

        list($left_menu_list, $right_menu_list) = MenuService::getMenuLinkList();
        $this['twig']->addGlobal('leftMenuList', $left_menu_list);
        $this['twig']->addGlobal('rightMenuList', $right_menu_list);

        $this['twig']->addGlobal('globalDomain', $_ENV['domain']);
        $this['twig']->addGlobal('sentryPublicKey', $_ENV['sentry_public_key']);

        $this->before(function (Request $request) {
            return UserPolicy::assertRestrictedPath($request);
        });

        $this->get('/', function () {
            $subRequest = Request::create('/posts/notice', 'GET');
            return $this->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        });
        $this->mount('/api', new APIController());
        $this->mount('/holidays', new HolidaysController());
        $this->mount('/organization', new OrganizationController());
        $this->mount('/payments', new PaymentsController());
        $this->mount('/posts', new PostsController());
        $this->mount('/press', new PressController());
        $this->mount('/programs', new ProgramsController());
        $this->mount('/receipts', new ReceiptsController());
        $this->mount('/users', new UsersController());
        $this->mount('/usersession', new UserSessionController());
        $this->mount('/weekly', new WeeklyController());

        $this->mount('/flextime', new FlexTimeController());
        $this->mount('/holidayadmin', new HolidayAdminController());
        $this->mount('/rooms', new RoomsController());
        $this->get('/focus', function () {
            $subRequest = Request::create('/rooms', 'GET', ['type' => 'focus']);
            return $this->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        });
        $this->mount('/support', new SupportController());
    }
}
