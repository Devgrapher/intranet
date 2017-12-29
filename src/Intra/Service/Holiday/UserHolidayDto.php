<?php

namespace Intra\Service\Holiday;

use Intra\Core\BaseDto;
use Symfony\Component\HttpFoundation\Request;

class UserHolidayDto extends BaseDto
{
    public $holidayid;
    public $request_date;
    public $uid;
    public $manager_uid;
    public $yearly;
    public $type;
    public $date;
    public $cost;
    public $keeper_uid;
    public $phone_emergency;
    public $memo;

    /**
     * @param $request Request
     * @param $yearly
     * @return UserHolidayDto
     */
    public static function importAddRequest($request, $yearly)
    {
        $holiday_raw = new self();
        $holiday_raw->date = $request->get('date');
        $holiday_raw->keeper_uid = $request->get('keeper_uid');
        $holiday_raw->manager_uid = $request->get('manager_uid');
        $holiday_raw->memo = $request->get('memo');
        $holiday_raw->phone_emergency = $request->get('phone_emergency');
        $holiday_raw->type = $request->get('type');
        $holiday_raw->cost = $request->get('cost');

        $holiday_raw->date = date('Y-m-d', strtotime($holiday_raw->date));
        $holiday_raw->phone_emergency = trim($holiday_raw->phone_emergency);
        $holiday_raw->yearly = $yearly;

        return $holiday_raw;
    }
}
