<?php
namespace Intra\Service\Payment;

class UserPaymentRequestFilter
{
    public static function getPayDateByStr($pay_type_str)
    {
        if ($pay_type_str == '7일' || $pay_type_str == '10일' || $pay_type_str == '25일') {
            $dest_day = preg_replace('/\D/', '', $pay_type_str);
            $cur_date = time();
            while (date('d', $cur_date) != $dest_day) {
                $cur_date = strtotime('next day', $cur_date);
            }

            return date('Y-m-d', $cur_date);
        }
        if ($pay_type_str == '월말일') {
            $cur_date = strtotime('last day of this month');

            return date('Y-m-d', $cur_date);
        }

        return '';
    }

    public static function parseMonth($month)
    {
        if ($month == null) {
            $month = date('Y-m');
        } else {
            $month = date('Y-m', strtotime($month));
        }

        return $month;
    }

    public static function parseDate($date)
    {
        if ($date == null) {
            $date = date('Y-m-d');
        } else {
            $date = date('Y-m-d', strtotime($date));
        }

        return $date;
    }
}
