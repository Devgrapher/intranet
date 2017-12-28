<?php

namespace Intra\Service\Receipt;

use Intra\Service\IntraDb;
use Ridibooks\Platform\Common\CsvResponse;

class UserReceiptsStat
{
    public function download($month)
    {
        $db = IntraDb::getGnfDb();

        $month = date('Y-m', strtotime($month));
        $nextmonth = date('Y-m', strtotime('+1 month', strtotime($month)));

        $tables = [
            'receipts.uid' => 'users.uid'
        ];
        $receipts = $db->sqlDicts(
            'select users.personcode, users.name, users.team, receipts.* from ? where str_to_date(?, "%Y-%m-%d") <= `date` and date < str_to_date(?, "%Y-%m-%d") order by uid asc, `date` asc, receiptid asc',
            sqlLeftJoin($tables),
            $month,
            $nextmonth
        );

        $csvs = [];
        //header

        $arr = ['사원번호', '이름', '팀', '날짜', '상호', '금액', '적요', '분류', '지불방식', '용도'];
        $csvs[] = $arr;

        foreach ($receipts as $receipt) {
            $arr = [
                $receipt['personcode'],
                $receipt['name'],
                $receipt['team'],
                $receipt['date'],
                $receipt['title'],
                $receipt['cost'],
                $receipt['note'],
                $receipt['type'],
                $receipt['payment'],
                $receipt['scope']
            ];
            $csvs[] = $arr;
        }

        return new CsvResponse($csvs, 'download.' . $month);
    }

    public function downloadYear($month)
    {
        $db = IntraDb::getGnfDb();

        $year = date('Y', strtotime($month));

        $tables = [
            'receipts.uid' => 'users.uid'
        ];
        $receipts = $db->sqlDicts(
            'select SUBSTR(`date`, 1, 7 ) as yearmonth, users.personcode, users.name, users.team, title, scope, type, payment, sum(cost) as cost from ? where year(`date`) = ? group by yearmonth, users.name, scope, type, payment ',
            sqlLeftJoin($tables),
            $year
        );

        $csvs = [];
        //header

        $arr = ['월', '사원번호', '이름', '팀', '상호', '금액', '적요', '분류', '지불방식', '용도'];
        $csvs[] = $arr;

        foreach ($receipts as $receipt) {
            $arr = [
                $receipt['yearmonth'] . '월',
                $receipt['personcode'],
                $receipt['name'],
                $receipt['team'],
                $receipt['title'],
                $receipt['scope'],
                $receipt['type'],
                $receipt['payment'],
                $receipt['cost'],
                $receipt['scope'],
            ];
            $csvs[] = $arr;
        }

        return new CsvResponse($csvs, 'downloadYear.' . $year);
    }
}
