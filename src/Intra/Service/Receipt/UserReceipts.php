<?php

namespace Intra\Service\Receipt;

use Intra\Service\IntraDb;
use Intra\Service\User\UserDto;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;

class UserReceipts
{
    /**
     * @var UserDto
     */
    private $user;

    /**
     * @param $user UserDto
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    public static function queryWeekend($month, $day)
    {
        $date = $month . '-' . $day;
        if (self::isWeekend($date)) {
            return '(<span style="color:red">주말</span>)';
        }
        return "(평일)";
    }

    private static function isWeekend($date)
    {
        return date('N', strtotime($date)) >= 6;
    }

    public function index($month = null)
    {
        $db = IntraDb::getGnfDb();

        $return = [];

        $return['user'] = $this->user;
        $uid = $this->user->uid;

        $prevmonth = date('Y-m', strtotime('-1 month', strtotime($month)));
        $nextmonth = date('Y-m', strtotime('+1 month', strtotime($month)));

        $return['month'] = $month;
        $return['prevmonth'] = $prevmonth;
        $return['nextmonth'] = $nextmonth;
        $return['receipts'] = $db->sqlDicts(
            'select * from receipts where uid = ? and str_to_date(?, "%Y-%m-%d") <= `date` and `date` < str_to_date(?, "%Y-%m-%d") order by `date` asc, receiptid asc',
            $uid,
            $month,
            $nextmonth
        );

        //용도별 통계

        $summaryDicts = $db->sqlDicts(
            'select scope, type, sum(cost) as cost, count(*) as count from receipts where uid = ? and str_to_date(?, "%Y-%m-%d") <= date and date < str_to_date(?, "%Y-%m-%d") group by scope, type order by scope, type',
            $uid,
            $month,
            $nextmonth
        );
        $summaryDictsByScope = $db->sqlDicts(
            'select scope, sum(cost) as cost, count(*) as count from receipts where uid = ? and str_to_date(?, "%Y-%m-%d") <= date and date < str_to_date(?, "%Y-%m-%d") group by scope order by scope',
            $uid,
            $month,
            $nextmonth
        );
        $summaryDictsByType = $db->sqlDicts(
            'select type, sum(cost) as cost, count(*) as count from receipts where uid = ? and str_to_date(?, "%Y-%m-%d") <= date and date < str_to_date(?, "%Y-%m-%d") group by type order by type',
            $uid,
            $month,
            $nextmonth
        );
        $allSummaryDict = $db->sqlDict(
            'select sum(cost) as cost, count(*) as count from receipts where uid = ? and str_to_date(?, "%Y-%m-%d") <= date and date < str_to_date(?, "%Y-%m-%d")',
            $uid,
            $month,
            $nextmonth
        );

        $columns = [
            '합계' => 1
        ];
        foreach ($summaryDictsByScope as $dict) {
            $columns[$dict['scope']] = 1;
        }
        foreach ($columns as $k => $v) {
            if (!$v) {
                unset($columns[$k]);
            }
        }

        $rows = [
            '저녁/휴일 식사비' => 0,
            '접대비' => 0,
            '야근교통비' => 0,
            '업무차 식음료비' => 0,
            '업무차 교통비' => 0,
            '사운드 리디' => 0,
            '리디캐시' => 0,
            '회식비' => 0,
            '도서 지원비' => 0,
            '사외수강비' => 0,
            'PAPER PRO 지원비' => 0,
            '기타' => 0,
            '합계' => 1
        ];
        foreach ($summaryDictsByType as $dict) {
            $rows[$dict['type']] = 1;
        }
        foreach ($rows as $k => $v) {
            if (!$v) {
                unset($rows[$k]);
            }
        }

        $costs = [];
        foreach ($rows as $row => $null) {
            foreach ($columns as $column => $null2) {
                $costs[$row][$column] = ['cost' => 0, 'count' => 0];
            }
        }
        foreach ($summaryDicts as $dict) {
            $costs[$dict['type']][$dict['scope']] = $dict;
        }
        foreach ($summaryDictsByScope as $dict) {
            $costs['합계'][$dict['scope']] = $dict;
        }
        foreach ($summaryDictsByType as $dict) {
            $costs[$dict['type']]['합계'] = $dict;
        }
        $costs['합계']['합계'] = $allSummaryDict;

        $return['columns'] = $columns;
        $return['costs'] = $costs;

        //지불방식별 통계

        $return['paymentCosts'] = $db->sqlDicts(
            'select payment, sum(cost) as cost from receipts where uid = ? and str_to_date(?, "%Y-%m-%d") <= date and date < str_to_date(?, "%Y-%m-%d") group by payment order by payment, type',
            $uid,
            $month,
            $nextmonth
        );
        $sum = 0;
        foreach ($return['paymentCosts'] as $cost) {
            $sum += $cost['cost'];
        }
        $return['paymentCosts'][] = ['payment' => '합계', 'cost' => $sum];

        $return['currentUid'] = $this->user->uid;
        $return['editable'] = (self::parseMonth() <= $month);
        if (UserPolicy::isSuperAdmin(UserSession::getSelfDto())) {
            $return['isSuperAdmin'] = 1;
            $return['editable'] |= 1;
        }

        $return['allCurrentUsers'] = UserDtoFactory::createAvailableUserDtos();
        $return['allUsers'] = UserDtoFactory::createAllUserDtos();

        return $return;
    }

    public static function parseMonth($month = null)
    {
        if ($month == null) {
            $cur_month = date('Y-m', strtotime('-15 day'));
            $month = $cur_month;
        } else {
            $month = date('Y-m', strtotime($month));
        }
        return $month;
    }

    public function add($month, $day, $title, $scope, $type, $cost, $payment, $note)
    {
        $db = IntraDb::getGnfDb();

        $row = [
            'title' => $title,
            'scope' => $scope,
            'type' => $type,
            'cost' => $cost,
            'payment' => $payment,
            'note' => $note
        ];
        $row['uid'] = $this->user->uid;
        $row['date'] = date('Y-m-d', strtotime($month . '-' . $day));
        if ($row['note'] == '저녁식사비' && self::isWeekend($row['date'])) {
            $row['note'] = '휴일식사비';
        }

        $this->assertAdd($row);

        $res = $db->sqlInsert('receipts', $row);

        if ($res) {
            return 1;
        }
        return '자료를 추가할 수 없습니다. 다시 확인해 주세요';
    }

    private function assertAdd($row)
    {
        $working_month = self::parseMonth();
        $timestamp_working_month = strtotime($working_month . '-1');

        if ($row['type'] == '저녁/휴일 식사비' && $row['cost'] > 8000) {
            throw new \Exception('"저녁/휴일 식사비"는 8000원 이하이어야합니다');
        } elseif ($row['type'] == 'PAPER PRO 지원비' && $row['cost'] > 24900) {
            throw new \Exception('"PAPER PRO 지원비"는 판매가의 10%에 해당하는 금액을 적어주세요.');
        }
        $timestamp_input_date = strtotime($row['date']);
        $timestamp_month = strtotime('first day of this month', $timestamp_working_month);
        if ($timestamp_input_date < $timestamp_month) {
            throw new \Exception('날짜를 확인해주세요');
        }

        if ($row['payment'] == null) {
            throw new \Exception('지불방식을 선택해주세요');
        }
    }

    public function del($receiptid)
    {
        $db = IntraDb::getGnfDb();

        $where = $this->getSafeEditableWhereCalues($receiptid);

        $res = $db->sqlDelete('receipts', $where);
        if ($res) {
            return 1;
        }
        return '삭제가 실패했습니다!';
    }

    public function edit($receiptid, $key, $value)
    {
        $db = IntraDb::getGnfDb();

        $update = [$key => $value];
        $where = $this->getSafeEditableWhereCalues($receiptid);

        $old_value = $db->sqlData('select ? from receipts where ?', sqlColumn($key), sqlWhere($where));
        if ($key == 'date') {
            $month_new = date('Ym', strtotime($value));
            $month_old = date('Ym', strtotime($old_value));
            if ($month_new != $month_old) {
                return $old_value;
            }
        }

        $db->sqlUpdate('receipts', $update, $where);
        $new_value = $db->sqlData('select ? from receipts where ?', sqlColumn($key), sqlWhere($where));
        if ($key == 'cost') {
            return number_format($new_value) . ' 원';
        }
        return $new_value;
    }

    private function getSafeEditableWhereCalues($receiptid)
    {
        $self = UserSession::getSelfDto();
        if (UserPolicy::isReceiptsAdmin($self)) {
            return ['receiptid' => $receiptid];
        }
        return ['receiptid' => $receiptid, 'uid' => $this->user->uid];
    }
}
