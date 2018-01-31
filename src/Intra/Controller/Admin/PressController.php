<?php
declare(strict_types=1);

namespace Intra\Controller\Admin;

use Intra\Service\Press\Press;
use Intra\Service\User\UserPolicy;
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

        $controller_collection->get('/', [$this, 'get']);
        $controller_collection->post('/', [$this, 'post']);
        $controller_collection->patch('/', [$this, 'patch']);
        $controller_collection->delete('/{id}', [$this, 'delete']);

        $controller_collection->before(function () {
            if (!UserPolicy::isPressManager(UserSession::getSelfDto())) {
                return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
            }
        });

        return $controller_collection;
    }

    public function get(Request $request, Application $app)
    {
        try {
            $user = UserSession::getSelfDto();
            $press_service = new Press($user);

            return $app['twig']->render('admin/press/index.twig', [
                'user' => $user,
                'press' => $press_service->getAll(),
                'manager' => UserSession::isPressManager(),
            ]);
        } catch (\Exception $e) {
            return Response::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function post(Request $request, Application $app)
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

    public function patch(Request $request, Application $app)
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

    public function delete(Request $request, Application $app)
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
