<?php
namespace Intra\Service\Holiday;

use DateTime;
use Intra\Model\HolidayModel;
use Intra\Model\HolidayAdjustModel;
use Intra\Service\IntraDb;
use Intra\Service\User\UserDto;

class UserHolidayPolicy
{
    /**
     * @var UserDto
     */
    private $user;
    private $user_holiday_model;

    public function __construct(UserDto $user)
    {
        $db = IntraDb::getGnfDb();
        $this->db = $db;

        $this->user = $user;
        $this->user_holiday_model = new HolidayModel();
    }

    public function getRemainCost($yearly)
    {
        $full_cost = $this->getAvailableCost($yearly);
        $used_cost = $this->getUsedCost($yearly);
        return $full_cost - $used_cost;
    }

    /**
     * @param $yearly
     *
     * @return int
     */
    public function getAvailableCost($yearly)
    {
        $info = $this->getDetailInfomationByYearly($yearly);
        if ($yearly == 0) {
            //금년입사
            $preusable_cost = $info['preusable_cost'];
            $full_cost = $preusable_cost;
        } elseif ($yearly == 1) {
            //작년입사(회계기준 조금 적용)
            $usable_cost = $info['base_holiday_count'];
            $preusable_cost = $info['preusable_cost'];
            $preused_cost_prev_year = $info['preused_cost_prev_year'];
            $full_cost = $usable_cost + $preusable_cost - $preused_cost_prev_year;
        } elseif ($yearly == 2) {
            //작년입사(회계기준 조금 적용)
            $usable_cost = 15;
            $preused_cost_prev_year = $info['preused_cost_prev_year'];
            $full_cost = $usable_cost - $preused_cost_prev_year;
        } else {
            //2014년시작 기점으로 완전한 회계기준 적용
            $full_cost = max(15, min(25, 15 + floor(($yearly - 2) / 2)));
        }
        $full_cost = $this->floorByZeroDotFive($full_cost);
        return $full_cost;
    }

    public function getDetailInfomationByYearly($yearly)
    {
        $ret = [];
        $on_date = $this->user->on_date;
        $yearly_begin_timestamp = $this->getYearlyBeginTimestamp($yearly);
        $yearly_end_timestamp = $this->getYearlyEndTimestamp($yearly);
        $ret['ondate'] = date('Y/m/d', strtotime($on_date));
        $ret['date_of_ondate'] = date('m/d', strtotime($on_date));
        if ($yearly == 0) {
            $from = date('Ymd', strtotime($on_date));
            $to = date('Ymd', min($yearly_end_timestamp, time()));
            $month_diff = floor(($to - $from) / 100);
            $ret['preusable_cost'] = max(0, $month_diff);
        }
        if ($yearly == 1) {
            //최대 12개아닌 11개까지 만 부여하기위해 '-1' 추가
            $next_year_of_ondate_timestamp = strtotime("+1 year", strtotime($on_date)) - 1;
            $end_of_yearmonth_range_timestamp = min($next_year_of_ondate_timestamp, time());

            $from = '00' . date('d', strtotime($on_date));
            $to = date('md', $end_of_yearmonth_range_timestamp);
            $month_diff = floor(($to - $from) / 100);
            $month_diff %= 12;
            $ret['preusable_cost'] = max(0, $month_diff);
        }
        if ($yearly == 1) {
            $beginOfPart = new DateTime($on_date);
            $endofPart = new DateTime();
            $endofPart->setTimestamp($yearly_begin_timestamp);

            $diff = $beginOfPart->diff($endofPart);
            $costByUpdatingYearRaw = 15 * $diff->days / 365;
            $costByUpdatingYear = $this->floorByZeroDotFive($costByUpdatingYearRaw);
            $ret['base_holiday_count'] = $costByUpdatingYear;
            $ret['worked_day_last_year'] = $diff->days;
        }
        if ($yearly == 1) {
            $ret['preused_cost_prev_year'] = floatval($this->getUsedCost($yearly - 1));
        }
        if ($yearly == 2) {
            $info = $this->getDetailInfomationByYearly($yearly - 1);
            $last_year_usable_holiday_count = $this->getUsedCost($yearly - 1) + $this->getUsedCost($yearly - 2);
            $last_year_remain_cost = $info['base_holiday_count'];
            $exceeded_cost = $last_year_usable_holiday_count - $last_year_remain_cost;

            $ret['last_year_remain_cost'] = $last_year_remain_cost;
            $ret['last_year_usable_holiday_count'] = $last_year_usable_holiday_count;

            $ret['preused_cost_prev_year'] = max(0, $exceeded_cost);
        }
        return $ret;
    }

    public function getYearlyBeginTimestamp($yearly)
    {
        $onDate = $this->user->on_date;
        $targetDate = strtotime("+{$yearly} year", strtotime($onDate));

        $year = date('Y', $targetDate);
        $yearlyBeginTimestamp = strtotime($year . '/1/1');
        return max(strtotime($onDate), $yearlyBeginTimestamp);
    }

    public function getYearlyEndTimestamp($yearly)
    {
        $onDate = $this->user->on_date;
        $targetDate = strtotime("+{$yearly} year", strtotime($onDate));

        $year = date('Y', $targetDate);
        $yearlyEndTimestamp = strtotime($year . '/12/31');
        return $yearlyEndTimestamp;
    }

    /**
     * @param $yearly
     *
     * @return int
     */
    public function getUsedCost($yearly)
    {
        $begin = date('Y/m/d', $this->getYearlyBeginTimestamp($yearly));
        $end = date('Y/m/d', $this->getYearlyEndTimestamp($yearly));
        return $this->user_holiday_model->getUsedCost($this->user, $begin, $end);
    }

    /**
     * @param $yearly
     *
     * @return int
     */
    public function getModCost($year)
    {
        return HolidayAdjustModel::where('uid', $this->user->uid)
            ->where('diff_year', '<=', $year)
            ->sum('diff');
    }

    /**
     * @param $fullCost
     *
     * @return float
     */
    private function floorByZeroDotFive($fullCost)
    {
        $fullCost = floor($fullCost * 2) / 2;
        return $fullCost;
    }
}
