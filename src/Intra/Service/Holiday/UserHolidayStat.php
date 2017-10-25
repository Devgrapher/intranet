<?php
namespace Intra\Service\Holiday;

use Intra\Model\HolidayModel;

class UserHolidayStat
{
    public function __construct()
    {
        $this->user_holiday_model = new HolidayModel();
    }

    public function getHolidaysAllUsers($year)
    {
        $begin = date($year . '/1/1');
        $end = date(($year) . '/12/31');
        $holidays = $this->user_holiday_model->getHolidaysByUserYearly(null, $begin, $end);

        return $holidays;
    }
}
