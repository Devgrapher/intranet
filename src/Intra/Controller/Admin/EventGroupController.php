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

class EventGroupController implements ControllerProviderInterface
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

    public function get(Request $request, Application $app)
    {
        if (!in_array('application/json', $request->getAcceptableContentTypes())) {
            return $app['twig']->render('admin/event_group/index.twig');
        }

        return $app->json(RoomService::getAllEventgroups());
    }

    public function post(Request $request, Application $app)
    {
        $event_group_id = intval($request->get('id'));
        $data = json_decode($request->getContent(), true);

        if ($event_group_id) {
            $result = RoomService::editEventGroup($event_group_id, $data);
        } else {
            $result = RoomService::addEventGroup($data);
        }

        return $app->json($result);
    }

    public function delete(Request $request)
    {
        $event_group_id = intval($request->get('id'));
        RoomService::deleteEventGroup($event_group_id);

        return new Response('success', Response::HTTP_OK);
    }
}
