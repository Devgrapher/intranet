<?php

namespace Intra\Controller;

use Intra\Service\Press\Press;
use Intra\Service\User\UserSession;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PressController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];
        $controller_collection->get('/', [$this, 'index']);
        $controller_collection->get('/list', [$this, 'getList']);
        $controller_collection->post('/add', [$this, 'add']);
        $controller_collection->post('/edit', [$this, 'edit']);
        $controller_collection->get('/del/{id}', [$this, 'del']);

        return $controller_collection;
    }

    public function index(Request $request, Application $app)
    {
        try {
            $user = UserSession::getSelfDto();
            $press_service = new Press($user);

            return $app['twig']->render('press/index.twig', [
                'user' => $user,
                'press' => $press_service->getAll(),
                'manager' => UserSession::isPressManager(),
            ]);
        } catch (\Exception $e) {
            return Response::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getList(Request $request, Application $app)
    {
        $page = $request->get('page', 1);
        $items_per_page = $request->get('items_per_page', 8);
        $user = UserSession::getSelfDto();
        $press_service = new Press($user);

        return $request->query->get('callback') . '(' . $press_service->getPressByPage($page, $items_per_page) . ')';
    }

    public function add(Request $request, Application $app)
    {
        try {
            $date = $request->get('date');
            $media = $request->get('media');
            $title = $request->get('title');
            $link_url = $request->get('link_url');
            $note = $request->get('note');
            $user = UserSession::getSelfDto();
            $press_service = new Press($user);
            $result = $press_service->add($date, $media, $title, $link_url, $note);
            if ($result === true) {
                return Response::create('success', Response::HTTP_OK);
            } else {
                return Response::create($result, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            return Response::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function edit(Request $request, Application $app)
    {
        try {
            $press_id = $request->get('id');
            $key = $request->get('key');
            $value = $request->get('value');

            $user = UserSession::getSelfDto();
            $press_service = new Press($user);
            $result = $press_service->edit($press_id, $key, $value);

            return Response::create($result, Response::HTTP_OK);
        } catch (\Exception $e) {
            return Response::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function del(Request $request, Application $app)
    {
        try {
            $press_id = $request->get('id');
            $user = UserSession::getSelfDto();
            $press_service = new Press($user);
            $result = $press_service->del($press_id);
            if ($result === true) {
                return Response::create('success', Response::HTTP_OK);
            } else {
                return Response::create($result, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            return Response::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
