<?php
declare(strict_types=1);

namespace Intra\Service\Room;

use Intra\Model\RoomEventGroupModel;
use Intra\Model\RoomEventModel;
use Intra\Model\RoomModel;

class RoomService
{
    const NOTICES = [
        'default' => '<p>정기 미팅, 장기 미팅은 BWS팀 철민님 통해 예약가능합니다.</p><p><b>회의실 예약 후 15분 이상 공실로 비어 있을 경우, 다른 직원분들이 사용할 수 있도록 기존 예약자의 소유권이 소멸됨을 안내 드립니다.</b></p>',
        'focus' => ' - FOCUS ROOM은 업무 집중 및 개인 휴식 공간입니다',
    ];

    const WARNING = [
        'default' => '',
        'focus' => ' 임직원이 공용으로 사용하는 파티션이므로 임의로 구조 변경하지 말아 주세요.'
    ];

    const EVENT_GROUP_START = 1000000;

    public static function getEvents($from, $to, $room_ids)
    {
        return RoomEventModel::whereIn('room_id', $room_ids)
            ->where('from', '<', $to)
            ->where('to', '>', $from)
            ->get([
                'id',
                'from as start_date',
                'to as end_date',
                'desc as text',
                'room_id',
            ])
            ->toArray();
    }

    public static function getAllEvents($from, $to, $room_ids)
    {
        $events = self::getEvents($from, $to, $room_ids);
        $event_groups = self::getEventGroups($from, $to, $room_ids);

        return array_merge($events, $event_groups);
    }

    private static function checkEventExists($from, $to, $room_id, $except_event_id = null)
    {
        $old_events = self::getAllEvents($from, $to, [$room_id]);
        foreach ($old_events as $event) {
            if ($event['id'] !== $except_event_id) {
                throw new \Exception('이미 다른 사람이 예약한 시간입니다 새로고침 해주세요.');
            }
        }
    }

    public static function addEvent(int $room_id, string $desc, string $from, string $to, int $uid)
    {
        self::checkEventExists($from, $to, $room_id);

        $new = RoomEventModel::create([
            'uid' => $uid,
            'room_id' => $room_id,
            'desc' => $desc,
            'from' => $from,
            'to' => $to,
        ]);

        return $new['id'];
    }

    public static function editEvent(int $id, array $update, int $uid = null)
    {
        $from = $update['from'];
        $to = $update['to'];
        $room_id = $update['room_id'];
        if (isset($from) && isset($to) && isset($room_id)) {
            self::checkEventExists($from, $to, $room_id, $id);
        }

        if (isset($uid)) {
            $where = ['id' => $id, 'uid' => $uid];
        } else {
            $where = ['id' => $id];
        }

        RoomEventModel::where($where)->update($update);
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

    public static function getAllEventGroups()
    {
        return RoomEventGroupModel::query()->get()->toArray();
    }

    private static function getEventGroups($from, $to, $room_ids)
    {
        $event_groups = RoomEventGroupModel::query()
            ->whereIn('room_id', $room_ids)
            ->where('to_date', '>=', $from)
            ->where('from_date', '<', $to)
            ->get()->toArray();

        $events = [];
        $start = strtotime($from);
        $end = strtotime($to);

        $day_index = $start;
        while ($day_index < $end) {
            foreach ($event_groups as $event_group) {
                $days = explode(',', $event_group['days_of_week']);
                if (in_array(date('w', $day_index), $days)) {
                    $from_date = date('Y-m-d', $day_index);
                    $start_date = $from_date . ' ' . $event_group['from_time'];
                    $end_date = $from_date . ' ' . $event_group['to_time'];
                    if (strtotime($start_date) < $start || $end < strtotime($end_date)) {
                        continue;
                    }

                    $events[] = [
                        'id' => $event_group['id'] + $day_index,
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'text' => $event_group['desc'],
                        'room_id' => $event_group['room_id'],
                        'days_of_week' => $event_group['days_of_week'],
                        'group' => true,
                    ];
                }
            }

            $day_index = strtotime('+1 day', $day_index);
        }

        return $events;
    }

    public static function addEventGroup(array $data)
    {
        $new = RoomEventGroupModel::create($data);

        return $new;
    }

    public static function editEventGroup(int $id, array $data)
    {
        $event = RoomEventGroupModel::find($id);
        $event->uid = $data['uid'];
        $event->room_id = $data['room_id'];
        $event->from_date = $data['from_date'];
        $event->to_date = $data['to_date'];
        $event->from_time = $data['from_time'];
        $event->to_time = $data['to_time'];
        $event->days_of_week = $data['days_of_week'];
        $event->desc = $data['desc'];
        $event->save();

        return $event->toArray();
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

    public static function getRoomSections(string $type)
    {
        if ($type === 'all') {
            $query = RoomModel::all();
        } else {
            $query = RoomModel::where('type', $type)->get();
        }

        return $query->toArray();
    }

    public static function getAllRoomSections()
    {
        return RoomModel::all()->toArray();
    }

    public static function addRoomSection(array $data)
    {
        $new = RoomModel::create([
            'type' => $data['type'] ?? 'default',
            'name' => $data['name'],
            'is_visible' => $data['is_visible'],
        ]);

        return $new;
    }

    public static function editRoomSection(int $id, array $data)
    {
        $room = RoomModel::find($id);
        $room->type = $data['type'] ?? 'default';
        $room->name = $data['name'];
        $room->is_visible = $data['is_visible'];
        $room->save();

        return $room->toArray();
    }

    public static function deleteRoomSection(int $id)
    {
        return RoomModel::find($id)->delete();
    }
}
