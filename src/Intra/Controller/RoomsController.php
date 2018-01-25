<?php

namespace Intra\Controller;

use Intra\Service\Room\RoomService;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RoomsController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];
        $controller_collection->get('/', [$this, 'index']);
        $controller_collection->get('/section', [$this, 'getSections']);
        $controller_collection->get('/event', [$this, 'getEvents']);
        $controller_collection->post('/event', [$this, 'addEvent']);
        $controller_collection->post('/event/{id}', [$this, 'editEvent']);
        $controller_collection->delete('/event/{id}', [$this, 'delEvent']);

        return $controller_collection;
    }

    public function index(Request $request, Application $app)
    {
        $type = $request->get('type', 'default');

        $rooms = RoomService::getRoomSections($type);
        $name = UserSession::getSelfDto()->name;

        return $app['twig']->render('rooms/index.twig', [
            'sections' => $rooms,
            'name' => $name,
        ]);
    }

    public function getSections(Request $request, Application $app)
    {
        $type = $request->get('type', 'default');
        if ($type === 'all') {
            $self = UserSession::getSelfDto();
            if (!UserPolicy::isPolicyRecipientEditable($self)) {
                return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
            }
        }

        return $app->json(RoomService::getRoomSections($type));
    }

    public function getEvents(Request $request, Application $app)
    {
        $now = date('Y-m-d');
        $from = $request->get('from', $now);
        $to = $request->get('to', date('Y-m-d', strtotime('+1 day', strtotime($now))));
        $room_ids = $request->get('room_ids', '');
        $room_ids = explode(',', $room_ids);

        $events = RoomService::getAllEvents($from, $to, $room_ids);

        return $app->json([
            'data' => $events,
        ]);
    }

    public function addEvent(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $room_id = $data['room_id'];
        $desc = $data['desc'];
        $from = $data['from'];
        $to = $data['to'];

        $user = UserSession::getSelfDto();
        $uid = $user->uid;

        try {
            return RoomService::addEvent($room_id, $desc, $from, $to, $uid);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function delEvent(Request $request)
    {
        $id = $request->get('id');
        $user = UserSession::getSelfDto();
        if (UserPolicy::isSuperAdmin($user)) {
            return RoomService::deleteEvent($id);
        } else {
            return RoomService::deleteEvent($id, $user->uid);
        }
    }

    public function editEvent(Request $request)
    {
        $id = $request->get('id');

        $data = json_decode($request->getContent(), true);
        $room_id = $data['room_id'];
        $desc = $data['desc'];
        $from = $data['from'];
        $to = $data['to'];

        $update = [
            'desc' => $desc,
            'from' => $from,
            'to' => $to,
            'room_id' => $room_id
        ];

        try {
            $user = UserSession::getSelfDto();
            if (UserPolicy::isSuperAdmin($user)) {
                RoomService::editEvent($id, $update);
            } else {
                RoomService::editEvent($id, $update, $user->uid);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return 1;
    }
}
