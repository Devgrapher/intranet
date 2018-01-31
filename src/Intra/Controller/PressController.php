<?php

namespace Intra\Controller;

use Intra\Service\Press\Press;
use Intra\Service\User\UserSession;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class PressController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];
        $controller_collection->get('/list', [$this, 'getList']);

        return $controller_collection;
    }

    public function getList(Request $request, Application $app)
    {
        $page = $request->get('page', 1);
        $items_per_page = $request->get('items_per_page', 8);
        $user = UserSession::getSelfDto();
        $press_service = new Press($user);

        return $request->query->get('callback') . '(' . $press_service->getPressByPage($page, $items_per_page) . ')';
    }
}
