<?php
declare(strict_types=1);

namespace Intra\Controller\Admin;

use Intra\Service\Room\RoomService;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RoomController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];

        $controller_collection->get('/', [$this, 'get']);
        $controller_collection->post('/', [$this, 'post']);
        $controller_collection->post('/{id}', [$this, 'post']);
        $controller_collection->delete('/{id}', [$this, 'delete']);

        $controller_collection->before(function () {
            if (!UserPolicy::isPolicyRecipientEditable(UserSession::getSelfDto())) {
                return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
            }
        });

        return $controller_collection;
    }

    public function get(Application $app)
    {
        return $app['twig']->render('admin/room/index.twig');
    }

    public function post(Request $request, Application $app)
    {
        $room_id = intval($request->get('id'));
        $data = json_decode($request->getContent(), true);

        if ($room_id) {
            $result = RoomService::editRoomSection($room_id, $data);
        } else {
            $result = RoomService::addRoomSection($data);
        }

        return $app->json($result);
    }

    public function delete(Request $request)
    {
        $room_id = intval($request->get('id'));
        RoomService::deleteRoomSection($room_id);

        return new Response('success', Response::HTTP_OK);
    }
}
