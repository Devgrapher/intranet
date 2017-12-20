<?php

namespace Intra\Controller;

use Intra\Service\Room\RoomService;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RoomsController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];
        $controller_collection->get('/', [$this, 'index']);
        $controller_collection->get('/event', [$this, 'get']);
        $controller_collection->post('/event', [$this, 'addEvent']);
        $controller_collection->post('/event/{id}', [$this, 'modEvent']);
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
            'description' => RoomService::DESCRIPTIONS[$type],
            'notice' => RoomService::NOTICES[$type],
            'warning' => RoomService::WARNING[$type]
        ]);
    }

    public function get(Request $request)
    {
        $now = date('Y-m-d');
        $from = $request->get('from', $now);
        $to = $request->get('to', date('Y-m-d', strtotime('+1 day', strtotime($now))));
        $room_ids = $request->get('room_ids', '');
        $room_ids = explode(',', $room_ids);

        $events = RoomService::getAllEvents($from, $to, $room_ids);
        return new JsonResponse([
            'data' => $events,
        ]);
    }

    public function addEvent(Request $request)
    {
        $room_id = $request->get('room_id');
        $desc = $request->get('desc');
        $from = $request->get('from');
        $to = $request->get('to');
        $user = UserSession::getSelfDto();
        $uid = $user->uid;

        try {
            return RoomService::addEvent($room_id, $desc, $from, $to, $uid);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function addEventGroup(Request $request)
    {
        $room_id = $request->get('room_id');
        $desc = $request->get('desc');
        $from_date = $request->get('from_date');
        $to_date = $request->get('to_date');
        $from_time = $request->get('from_time');
        $to_time = $request->get('to_time');
        $days_of_week = $request->get('days_of_week');
        $user = UserSession::getSelfDto();
        $uid = $user->uid;

        try {
            return RoomService::addEventGroup($room_id, $desc, $from_date, $to_date, $from_time, $to_time, $days_of_week, $uid);
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

    public function modEvent(Request $request)
    {
        $id = $request->get('id');
        $desc = $request->get('desc');
        $from = $request->get('from');
        $to = $request->get('to');
        $room_id = $request->get('room_id');

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
            return '예약 변경이 실패했습니다. 개발팀에 문의주세요';
        }

        return 1;
    }
}
