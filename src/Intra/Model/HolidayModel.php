<?php

namespace Intra\Model;

use Intra\Service\Holiday\UserHolidayDto;
use Intra\Service\IntraDb;
use Intra\Service\User\UserDto;

class HolidayModel
{
    public static $const = [
        'types' => [
            '연차',
            '오전반차',
            '오후반차',
            '공가',
            '경조',
            '대체휴가',
            '대체오전반차',
            '대체오후반차',
            '무급휴가',
            '무급오전반차',
            '무급오후반차',
            'PWT'
        ],
        'memos' => [
            '개인용무',
            '병원진료',
            '예비군훈련',
            '경조사',
            '기타'
        ],
    ];

    public function __construct()
    {
        $this->db = IntraDb::getGnfDb();
    }

    public function getUsedCost(UserDto $user, $begin, $end)
    {
        $where = ['uid' => $user->uid, 'hidden' => 0, 'date' => sqlBetween($begin, $end)];

        return max(0, $this->db->sqlData('select sum(cost) from holidays where ?', sqlWhere($where)));
    }

    /**
     * @param UserDto $user
     * @param         $begin
     * @param         $end
     *
     * @return UserHolidayDto[]
     */
    public function getHolidaysByUser($user, $begin, $end)
    {
        if ($user == null) {
            $where = [
                'hidden' => 0,
                'date' => sqlBetween($begin, $end),
            ];
        } else {
            $where = [
                'holidays.uid' => $user->uid,
                'hidden' => 0,
                'date' => sqlBetween($begin, $end),
            ];
        }

        return $this->db->sqlObjects('
          select
            holidays.*,
            user.personcode,
            user.name as uid_name,
            manager.name as manager_uid_name,
            keeper.name as keeper_uid_name
          from holidays
            LEFT JOIN users user on holidays.uid = user.uid
            LEFT JOIN users manager on holidays.manager_uid = manager.uid
            LEFT JOIN users keeper on holidays.keeper_uid = keeper.uid
          where ? order by holidays.uid asc, date asc', sqlWhere($where));
    }

    /**
     * @param         $team_name
     * @param         $begin
     * @param         $end
     *
     * @return UserHolidayDto[]
     */
    public function getHolidaysByTeam($team_name, $begin, $end)
    {
        $where = [
            'hidden' => 0,
            'user.team' => $team_name,
            'date' => sqlBetween($begin, $end),
        ];

        return $this->db->sqlObjects('
          select
            holidays.*,
            user.personcode,
            user.name as uid_name,
            manager.name as manager_uid_name,
            keeper.name as keeper_uid_name
          from holidays
            LEFT JOIN users user on holidays.uid = user.uid
            LEFT JOIN users manager on holidays.manager_uid = manager.uid
            LEFT JOIN users keeper on holidays.keeper_uid = keeper.uid
          where ? order by holidays.uid asc, date asc', sqlWhere($where));
    }

    public function add(UserHolidayDto $holidayRaw)
    {
        $holiday = get_object_vars($holidayRaw);
        foreach ($holiday as $k => $v) {
            if (is_null($v)) {
                unset($holiday[$k]);
            }
        }

        $this->db->sqlInsert('holidays', $holiday);

        return $this->db->insert_id();
    }

    public function hide($holidayid, $uid)
    {
        $update = ['hidden' => 1];

        return $this->db->sqlUpdate('holidays', $update, compact('holidayid', 'uid'));
    }

    public function edit($holidayid, $uid, $key, $value)
    {
        $update = [$key => $value];
        $where = [
            'holidayid' => $holidayid,
            'uid' => $uid
        ];
        $this->db->sqlUpdate('holidays', $update, $where);
    }

    /**
     * @param $holidayid
     * @param $uid
     *
     * @return UserHolidayDto
     */
    public function get($holidayid, $uid)
    {
        return head($this->gets([$holidayid], $uid));
    }

    /**
     * @param $holidayids
     * @param $uid
     *
     * @return UserHolidayDto[]
     */
    public function gets(array $holidayids, $uid)
    {
        $where = ['holidayid' => $holidayids, 'uid' => $uid];

        return $this->db->sqlObjects('select * from holidays where ? order by date asc', sqlWhere($where));
    }

    public function getsToday()
    {
        $where = [
            'date' => date('Y-m-d'),
            'hidden' => 0
        ];

        return $this->db->sqlObjects('select uid, `type` from holidays where ?', sqlWhere($where));
    }

    public function isAllowedToAdd($date, $uid, $cost)
    {
        $date = date('Y-m-d', strtotime($date));
        $where = [
            'date' => $date,
            'uid' => $uid,
            'hidden' => 0
        ];

        return $this->db->sqlData('select sum(cost) >= (1 - ' . intval($cost) . ') from holidays where ?', sqlWhere($where));
    }

    public function isDuplicated($date, $uid)
    {
        $date = date('Y-m-d', strtotime($date));
        $where = [
            'date' => $date,
            'uid' => $uid,
            'hidden' => 0
        ];

        return $this->db->sqlCount('holidays', $where);
    }

    public function isDuplicateInDateRangeByType($this_month, $next_month, $type, $uid)
    {
        $where = [
            'date' => sqlRange($this_month, $next_month),
            'type' => $type,
            'uid' => $uid,
            'hidden' => 0
        ];

        return $this->db->sqlCount('holidays', $where);
    }

    public function getDuplicatedType($date, $uid)
    {
        $date = date('Y-m-d', strtotime($date));
        $where = [
            'date' => $date,
            'uid' => $uid,
            'hidden' => 0
        ];

        return $this->db->sqlData('select type from holidays where ?', sqlWhere($where));
    }
}
