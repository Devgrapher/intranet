<?php

namespace Intra\Controller;

use Intra\Service\IntraDb;
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
        $controller_collection->get('/get', [$this, 'get']);
        $controller_collection->post('/add', [$this, 'add']);
        $controller_collection->post('/del/id/{id}', [$this, 'del']);
        $controller_collection->post('/mod', [$this, 'mod']);
        return $controller_collection;
    }

    public function index(Request $request, Application $app)
    {
        $db = IntraDb::getGnfDb();
        $type = $request->get('type');

        if (!strlen($type)) {
            $type = 'default';
        }

        $where = [
            'is_visible' => 1,
            'type' => $type,
        ];

        $rooms = $db->sqlDicts('select * from rooms  where ?', sqlWhere($where));
        $name = UserSession::getSelfDto()->name;

        $descriptions = [
            'default' => '#회의실 예약 방법
1. 인트라넷에 로그인 후, [회의실 예약] 메뉴를 눌러주세요.
2. 예약할 회의실을 선택한 후, 사용하실 시간을 드래그하여 지정해주세요.
3. 예약자와 예약내용을 기재하시고, ‘Enter’키를 눌러 저장해주세요.
4. 삭제 시 내가 저장한 시간을 클릭하고 왼쪽에 ‘휴지통’ 버튼을 눌러주세요. (수정 시에는 ‘펜’ 버튼을 눌러주세요.)

*주의사항
- 회의실 예약 후, 미팅이 취소된 경우 다른 사람을 위해 예약 내역을 꼭 삭제해주세요.
- 회의실 예약 후 15분 이상 공실로 비어 있을 경우, 다른 직원분들이 사용할 수 있도록 기존 예약자의 소유권이 소멸됨을 안내 드립니다.

- 모든 외부 손님의 미팅은 10층에서 진행해주세요. (예. 출판사 미팅, 면접 등)
- 11층의 중요한 외부 손님(예. VIP, 중요한 기자 등) 방문의 경우, 방문자 및 방문내용에 대해 대표님 서면 승인이 있어야 출입이 가능하오니 유념하여 주세요.
* 관련 문의는 인사팀에 해주시기 바랍니다.',
        ];

        $notices = [
            'default' => '<p>정기 미팅, 장기 미팅은 인사팀 철민님 통해 예약가능합니다.</p><p><b>회의실 예약 후 15분 이상 공실로 비어 있을 경우, 다른 직원분들이 사용할 수 있도록 기존 예약자의 소유권이 소멸됨을 안내 드립니다.</b></p>',
            'focus' => ' - FOCUS ROOM은 업무 집중 및 개인 휴식 공간입니다',
        ];

        $warning = [
            'default' => '',
            'focus' => ' 임직원이 공용으로 사용하는 파티션이므로 임의로 구조 변경하지 말아 주세요.'
        ];

        return $app['twig']->render('rooms/index.twig', [
            'rooms' => $rooms,
            'name' => $name,
            'description' => $descriptions[$type],
            'notice' => $notices[$type],
            'warning' => $warning[$type]
        ]);
    }

    public function get(Request $request)
    {
        $db = IntraDb::getGnfDb();

        $from = $request->get('from');
        $to = $request->get('to');
        $room_ids = $request->get('room_ids');
        $room_ids = explode(',', $room_ids);

        if (count($room_ids) == 0) {
            return new JsonResponse([]);
        }

        $where = [
            'deleted' => 0,
            'from' => sqlGreaterEqual($from),
            'to' => sqlLesser($to),
            'room_id' => $room_ids,
        ];

        $events = $db->sqlDicts('select * from room_events where ?', sqlWhere($where));
        $datas = [];
        $datas = $this->addDefaultReservation($from, $datas, $room_ids);

        foreach ($events as $event) {
            $datas[] = [
                'id' => $event['id'],
                'start_date' => $event['from'],
                'end_date' => $event['to'],
                'text' => $event['desc'],
                'details' => $event['desc'],
                'room_id' => $event['room_id'],
            ];
        }
        $return['data'] = $datas;

        return new JsonResponse($return);
    }

    public function add(Request $request)
    {
        $room_id = $request->get('room_id');
        $desc = $request->get('desc');
        $from = $request->get('from');
        $to = $request->get('to');
        $user = UserSession::getSelfDto();
        $uid = $user->uid;

        $db = IntraDb::getGnfDb();

        $where = [
            'room_id' => $room_id,
            'deleted' => 0,
            sqlOr(
                [
                    'from' => sqlLesserEqual($from),
                    'to' => sqlGreater($from),
                ],
                [
                    'from' => sqlLesser($to),
                    'to' => sqlGreaterEqual($to),
                ]
            ),
        ];

        $overlapped_events = $db->sqlDicts('select * from room_events where ?', sqlWhere($where));
        if (count($overlapped_events) > 0) {
            return '이미 다른 사람이 예약한 시간입니다. 새로고침 해주세요.';
        }

        $dat = [
            'room_id' => $room_id,
            'desc' => $desc,
            'from' => $from,
            'to' => $to,
            'uid' => $uid
        ];

        $db->sqlInsert('room_events', $dat);
        return $db->insert_id();
    }

    public function del(Request $request)
    {
        $id = $request->get('id');
        $db = IntraDb::getGnfDb();
        $user = UserSession::getSelfDto();
        $uid = $user->uid;

        $update = ['deleted' => 1];
        if ($user->is_admin) {
            $where = ['id' => $id];
        } else {
            $where = ['id' => $id, 'uid' => $uid];
        }

        return $db->sqlUpdate('room_events', $update, $where);
    }

    public function mod(Request $request)
    {
        $db = IntraDb::getGnfDb();
        $id = $request->get('id');
        $desc = $request->get('desc');
        $from = $request->get('from');
        $to = $request->get('to');
        $room_id = $request->get('room_id');
        $user = UserSession::getSelfDto();
        $uid = $user->uid;

        if ($user->is_admin) {
            $where = ['id' => $id];
        } else {
            $where = ['id' => $id, 'uid' => $uid];
        }
        $update = ['desc' => $desc, 'from' => $from, 'to' => $to, 'room_id' => $room_id];
        if ($db->sqlUpdate('room_events', $update, $where)) {
            return 1;
        }

        return '예약 변경이 실패했습니다. 개발팀에 문의주세요';
    }

    /**
     * @param $from
     * @param $datas
     * @param $room_ids
     *
     * @return array
     */
    private function addDefaultReservation($from, $datas, $room_ids)
    {
        $room_11_4 = 15;
        if (in_array($room_11_4, $room_ids)) {
            /*
        //플랫폼팀 주간미팅
        if (date('w', strtotime($from)) == 1) {
            $datas[] =
                [
                    'id' => 0,
                    'start_date' => $from . ' 11:30:00',
                    'end_date' => $from . ' 12:30:00',
                    'text' => '[예약자] 박주현 [예약내용] 주간미팅',
                    'details' => '[예약자] 박주현 [예약내용] 주간미팅',
                    'room_id' => $room_11_4,
                ];
        }
            */
        }
        return $datas;
    }
}
