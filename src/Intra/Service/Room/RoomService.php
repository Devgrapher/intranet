<?php
declare(strict_types=1);

namespace Intra\Service\Room;

use Intra\Model\RoomEventGroupModel;
use Intra\Model\RoomEventModel;
use Intra\Model\RoomModel;

class RoomService
{
    const DESCRIPTIONS = [
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

    const NOTICES = [
        'default' => '<p>정기 미팅, 장기 미팅은 BWS팀 철민님 통해 예약가능합니다.</p><p><b>회의실 예약 후 15분 이상 공실로 비어 있을 경우, 다른 직원분들이 사용할 수 있도록 기존 예약자의 소유권이 소멸됨을 안내 드립니다.</b></p>',
        'focus' => ' - FOCUS ROOM은 업무 집중 및 개인 휴식 공간입니다',
    ];

    const WARNING = [
        'default' => '',
        'focus' => ' 임직원이 공용으로 사용하는 파티션이므로 임의로 구조 변경하지 말아 주세요.'
    ];

    const EVENT_GROUP_START = 1000000;

    public static function addEvent(int $room_id, string $desc, string $from, string $to, int $uid)
    {
        $old_events = self::getAllEvents($from, $to, [$room_id]);
        if (count($old_events) > 0) {
            throw new \Exception('이미 다른 사람이 예약한 시간입니다 새로고침 해주세요.');
        }

        $new = RoomEventModel::create([
            'uid' => $uid,
            'room_id' => $room_id,
            'desc' => $desc,
            'from' => $from,
            'to' => $to,
        ]);

        return $new['id'];
    }

    public static function addEventGroup(int $room_id, string $desc, string $from_date, string $to_date,
                                         string $from_time, string $to_time, string $days_of_week, int $uid)
    {
        $new = RoomEventGroupModel::create([
            'uid' => $uid,
            'room_id' => $room_id,
            'desc' => $desc,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'from_time' => $from_time,
            'to_time' => $to_time,
            'days_of_week' => $days_of_week,
        ]);

        return $new['id'];
    }

    public static function deleteEvent(int $id, int $uid = null)
    {
        if (isset($uid)) {
            $where = ['id' => $id, 'uid' => $uid];
        } else {
            $where = ['id' => $id];
        }

        return RoomEventModel::where($where)->delete();
    }

    public static function deleteEventGroup(int $id, int $uid = null)
    {
        if (isset($uid)) {
            $where = ['id' => $id, 'uid' => $uid];
        } else {
            $where = ['id' => $id];
        }

        return RoomEventGroupModel::where($where)->delete();
    }

    public static function editEvent(int $id, array $update, int $uid = null)
    {
        if (isset($uid)) {
            $where = ['id' => $id, 'uid' => $uid];
        } else {
            $where = ['id' => $id];
        }

        RoomEventModel::where($where)->update($update);
    }

    public static function getAllEvents($from, $to, $room_ids)
    {
        $events = RoomEventModel::whereIn('room_id', $room_ids)
            ->where('from', '>=', $from)
            ->where('to', '<', $to)
            ->get([
                'id',
                'from as start_date',
                'to as end_date',
                'desc as text',
                'desc as details',
                'room_id',
            ])
            ->toArray();

        $event_groups = RoomEventGroupModel::whereIn('room_id', $room_ids)
            ->where('to_date', '>=', $from)
            ->where('from_date', '<=', $to)
            ->get()
            ->toArray();

        $start = strtotime($from);
        $end = strtotime($to);
        while ($start < $end) {
            foreach ($event_groups as $event_group) {
                $days = explode(',', $event_group['days_of_week']);
                if (in_array(date('w', $start), $days)) {
                    $from_date = date('Y-m-d', $start);
                    $events[] = [
                        'id' => $event_group['id'] + $start,
                        'start_date' => $from_date . ' ' . $event_group['from_time'],
                        'end_date' => $from_date . ' ' . $event_group['to_time'],
                        'text' => $event_group['desc'],
                        'room_id' => $event_group['room_id'],
                        'days_of_week' => $event_group['days_of_week'],
                        'group' => true,
                    ];
                }
            }

            $start = strtotime('+1 day', $start);
        }

        return $events;
    }

    public static function getRoomSections(string $type)
    {
        return RoomModel::where('is_visible', 1)
            ->where('type', $type)
            ->get([
                'id as key',
                'name as label'
            ])
            ->toArray();
    }
}
