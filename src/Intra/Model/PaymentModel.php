<?php

namespace Intra\Model;

use Intra\Core\BaseModel;

class PaymentModel extends BaseModel
{
    public function getPayments($uid, $month)
    {
        $nextmonth = date('Y-m', strtotime('+1 month', strtotime($month)));

        $table = [
            'payments.uid' => 'users.uid'
        ];
        $where = [
            'payments.uid' => $uid,
            sqlOr(
                ['status' => ['결제 대기중']],
                ['request_date' => sqlRange($month . '-1', $nextmonth . '-1')]
            )
        ];

        return $this->db->sqlDicts(
            'select payments.*, users.name from ? where ? order by `status`, `request_date` asc, paymentid asc',
            sqlLeftJoin($table),
            sqlWhere($where)
        );
    }

    public function queuedPayments()
    {
        $table = [
            'payments.uid' => 'users.uid'
        ];
        $where = [
            'status' => ["결제 대기중"]
        ];

        return $this->db->sqlDicts(
            'select payments.*, users.name from ? where ? order by `pay_date` asc, paymentid asc',
            sqlLeftJoin($table),
            sqlWhere($where)
        );
    }

    public function queuedPaymentsByManager($uid, $is_accepted)
    {
        $table = [
            'payments.uid' => 'users.uid',
            'payments.paymentid' => [
                'payment_accept.paymentid',
                'payment_accept.user_type' => 'manager',
            ],
        ];
        $where = [
            'status' => ["결제 대기중"],
            'payments.manager_uid' => $uid,
        ];

        if ($is_accepted) {
            $where['payment_accept.paymentid'] = sqlNot(null);
        } else {
            $where['payment_accept.paymentid'] = null;
        }

        return $this->db->sqlDicts(
            'select payments.*, users.name from ? where ? order by `status`,`pay_date` asc, paymentid asc',
            sqlLeftJoin($table),
            sqlWhere($where)
        );
    }

    public function todayQueuedCost()
    {
        $where = $this->getTodayQueuedWhere();

        return number_format(
            $this->db->sqlData(
                'select sum(price) from payments where ? order by `pay_date` asc, paymentid asc',
                sqlWhere($where)
            )
        );
    }

    public function todayConfirmedQueuedCost()
    {
        $table = [
            'payments.uid' => 'users.uid',
            'payments.paymentid' => [
                'payment_accept.paymentid',
                'payment_accept.user_type' => 'manager',
            ],
        ];
        $where = $this->getTodayConfirmedQueuedWhere();

        return number_format(
            $this->db->sqlData(
                'select sum(price) from ? where ? order by `pay_date` asc, payment_accept.paymentid asc',
                sqlLeftJoin($table),
                sqlWhere($where)
            )
        );
    }

    public function todayUnconfirmedQueuedCost()
    {
        $table = [
            'payments.uid' => 'users.uid',
            'payments.paymentid' => [
                'payment_accept.paymentid',
                'payment_accept.user_type' => 'manager',
            ],
        ];
        $where = $this->getTodayUnconfirmedQueuedWhere();

        return number_format(
            $this->db->sqlData(
                'select sum(price) from ? where ? order by `pay_date` asc, payment_accept.paymentid asc',
                sqlLeftJoin($table),
                sqlWhere($where)
            )
        );
    }

    /**
     * @return array
     */
    private function getTodayQueuedWhere()
    {
        return [
            'status' => ["결제 대기중"],
            'pay_date' => sqlRange(
                date('Y/m/d'),
                date('Y/m/d', strtotime('+1 day'))
            )
        ];
    }

    private function getTodayConfirmedQueuedWhere()
    {
        return [
            'status' => ["결제 대기중"],
            'pay_date' => sqlRange(
                date('Y/m/d'),
                date('Y/m/d', strtotime('+1 day'))
            ),
            'payment_accept.paymentid' => sqlNot(null),
        ];
    }

    private function getTodayUnconfirmedQueuedWhere()
    {
        return [
            'status' => ["결제 대기중"],
            'pay_date' => sqlRange(
                date('Y/m/d'),
                date('Y/m/d', strtotime('+1 day'))
            ),
            'payment_accept.paymentid' => null,
        ];
    }

    private function getPayMonthWhere($pay_month)
    {
        return ['month' => $pay_month];
    }

    private function getPayMonthQueuedWhere($pay_month)
    {
        return [
            'status' => ["결제 대기중"],
            'month' => $pay_month,
        ];
    }

    public function todayQueuedCount()
    {
        $where = $this->getTodayQueuedWhere();

        return $this->db->sqlCount('payments', $where);
    }

    public function todayConfirmedQueuedCount()
    {
        $table = [
            'payments.uid' => 'users.uid',
            'payments.paymentid' => [
                'payment_accept.paymentid',
                'payment_accept.user_type' => 'manager',
            ],
        ];
        $where = $this->getTodayConfirmedQueuedWhere();

        return $this->db->sqlCount(sqlLeftJoin($table), $where);
    }

    public function todayUnconfirmedQueuedCount()
    {
        $table = [
            'payments.uid' => 'users.uid',
            'payments.paymentid' => [
                'payment_accept.paymentid',
                'payment_accept.user_type' => 'manager',
            ],
        ];
        $where = $this->getTodayUnconfirmedQueuedWhere();

        return $this->db->sqlCount(sqlLeftJoin($table), $where);
    }

    public function add($payment_insert)
    {
        $this->db->sqlInsert('payments', $payment_insert);

        return $this->db->insert_id();
    }

    public function getAllPayments($month)
    {
        $nextmonth = date('Y-m-1', strtotime('+1 month', strtotime($month)));

        $tables = [
            'payments.uid' => 'users.uid'
        ];

        return $this->db->sqlDicts(
            'select users.name, payments.* from ? where `pay_date` between ? and ? order by pay_date asc, uid asc',
            sqlLeftJoin($tables),
            $month,
            $nextmonth
        );
    }

    public function getAllPaymentsByTaxDate($month)
    {
        $nextmonth = date('Y-m-1', strtotime('+1 month', strtotime($month)));

        $tables = [
            'payments.uid' => 'users.uid'
        ];

        return $this->db->sqlDicts(
            'select users.name, payments.* from ? where `tax_date` between ? and ? order by pay_date asc, uid asc',
            sqlLeftJoin($tables),
            $month,
            $nextmonth
        );
    }

    public function del($paymentid)
    {
        return $this->db->sqlDelete('payments', compact('paymentid'));
    }

    public function update($paymentid, $key, $value)
    {
        $update = [$key => $value];
        $where = compact('paymentid');
        $this->db->sqlUpdate('payments', $update, $where);
    }

    public function getPayment($paymentid, $uid)
    {
        $where = [
            'paymentid' => $paymentid,
            sqlOr(
                ['uid' => $uid],
                ['manager_uid' => $uid]
            )
        ];

        return $this->db->sqlDict('select * from payments where ?', sqlWhere($where));
    }

    public function getPaymentWithoutUid($paymentid)
    {
        $where = compact('paymentid');

        return $this->db->sqlDict('select * from payments where ?', sqlWhere($where));
    }

    public static function getPaydayIsAfter3days()
    {
        $on_3days_ago = date('Y/m/d 00:00:00', strtotime('-3 day'));
        $on_2days_ago = date('Y/m/d 00:00:00', strtotime('-2 day'));
        $where = [
            'pay_date' => sqlBetween($on_3days_ago, $on_2days_ago),
            'payments.status' => sqlNot('결제 완료'),
        ];

        return self::getDb()->sqlDicts('select * from payments where ?', sqlWhere($where));
    }

    public static function getManagerNotYetAccepts()
    {
        $table = [
            'payments.paymentid' => ['payment_accept.paymentid', 'payment_accept.user_type' => 'manager']
        ];
        $where = [
            'payment_accept.id' => null,
            'payments.status' => sqlNot('결제 완료'),
        ];

        return self::getDb()->sqlDicts('select payments.* from ? where ?', sqlLeftJoin($table), sqlWhere($where));
    }

    public function todayQueued()
    {
        $table = [
            'payments.uid' => 'users.uid'
        ];
        $where = $this->getTodayQueuedWhere();

        return $this->db->sqlDicts(
            'select payments.*, users.name from ? where ? order by `pay_date` asc, paymentid asc',
            sqlLeftJoin($table),
            sqlWhere($where)
        );
    }

    public function todayConfirmedQueued()
    {
        $table = [
            'payments.uid' => 'users.uid',
            'payments.paymentid' => [
                'payment_accept.paymentid',
                'payment_accept.user_type' => 'manager',
            ],
        ];
        $where = $this->getTodayConfirmedQueuedWhere();

        return $this->db->sqlDicts(
            'select payments.*, users.name from ? where ? order by `pay_date` asc, paymentid asc',
            sqlLeftJoin($table),
            sqlWhere($where)
        );
    }

    public function todayUnconfirmedQueued()
    {
        $table = [
            'payments.uid' => 'users.uid',
            'payments.paymentid' => [
                'payment_accept.paymentid',
                'payment_accept.user_type' => 'manager',
            ],
        ];
        $where = $this->getTodayUnconfirmedQueuedWhere();

        return $this->db->sqlDicts(
            'select payments.*, users.name from ? where ? order by `pay_date` asc, paymentid asc',
            sqlLeftJoin($table),
            sqlWhere($where)
        );
    }

    public function payMonth($pay_month)
    {
        $table = [
            'payments.uid' => 'users.uid'
        ];
        $where = $this->getPayMonthWhere($pay_month);

        return $this->db->sqlDicts(
            'select payments.*, users.name from ? where ? order by `pay_date` asc, paymentid asc',
            sqlLeftJoin($table),
            sqlWhere($where)
        );
    }

    public function payMonthQueued($pay_month)
    {
        $table = [
            'payments.uid' => 'users.uid'
        ];
        $where = $this->getPayMonthQueuedWhere($pay_month);

        return $this->db->sqlDicts(
            'select payments.*, users.name from ? where ? order by `pay_date` asc, paymentid asc',
            sqlLeftJoin($table),
            sqlWhere($where)
        );
    }

    public function getPaymentsWithOption($month, $where)
    {
        $nextmonth = date('Y-m', strtotime('+1 month', strtotime($month)));

        $table = [
            'payments.uid' => 'users.uid'
        ];

        $where['request_date'] = sqlRange($month . '-1', $nextmonth . '-1');

        return $this->db->sqlDicts(
            'select payments.*, users.name from ? where ? order by `status`, `request_date` asc, paymentid asc',
            sqlLeftJoin($table),
            sqlWhere($where)
        );
    }

    public function updateUuid($paymentid)
    {
        $sql = "SELECT request_date,(select count(*) from payments where request_date = payments_root.request_date) `count` FROM payments payments_root where paymentid = ?";
        $dict = $this->db->sqlDict($sql, $paymentid);
        if (!$dict) {
            throw  new \Exception('UUID 업데이트 중 정보 얻기가 실패했습니다.');
        }
        $request_date = $dict['request_date'];
        $count = $dict['count'];
        $uuid = date_create($request_date)->format('Ymd') . sprintf('%04d', $count);
        $update = [
            'uuid' => $uuid
        ];
        $where = [
            'paymentid' => $paymentid
        ];
        $this->db->sqlUpdate('payments', $update, $where);
    }

    public function getAllPaymentsByActiveMonth($month)
    {
        $month_str = date('Y-m', strtotime($month));

        $tables = [
            'payments.uid' => 'users.uid'
        ];

        return $this->db->sqlDicts(
            'select users.name, payments.* from ? where payments.`month` = ? order by pay_date asc, uid asc',
            sqlLeftJoin($tables),
            $month_str
        );
    }

    public function getAllPaymentsByActiveTeam($team)
    {
        $tables = [
            'payments.uid' => 'users.uid'
        ];

        return $this->db->sqlDicts(
            'select users.name, payments.* from ? where payments.`team` = ? order by pay_date asc, uid asc',
            sqlLeftJoin($tables),
            $team
        );
    }

    public function getAllPaymentsByActiveCategory($category)
    {
        $tables = [
            'payments.uid' => 'users.uid'
        ];

        return $this->db->sqlDicts(
            'select users.name, payments.* from ? where payments.`category` = ? order by pay_date asc, uid asc',
            sqlLeftJoin($tables),
            $category
        );
    }

    public function getAllPaymentsByActiveRequestDate($begin_date, $end_date)
    {
        $tables = [
            'payments.uid' => 'users.uid'
        ];

        return $this->db->sqlDicts(
            'select users.name, payments.* from ? where payments.`request_date` >= ? and `request_date` <= ? order by pay_date asc, uid asc',
            sqlLeftJoin($tables),
            $begin_date,
            $end_date
        );
    }

    public function getAllPaymentsByPayDate($begin_date, $end_date)
    {
        $tables = [
            'payments.uid' => 'users.uid'
        ];

        return $this->db->sqlDicts(
            'select users.name, payments.* from ? where payments.`pay_date` >= ? and `pay_date` <= ? order by pay_date asc, uid asc',
            sqlLeftJoin($tables),
            $begin_date,
            $end_date
        );
    }
}
