<?php

namespace Intra\Service\FlexTime;

use Intra\Model\FlexTimeModel;
use Intra\Service\User\UserJoinService;

class FlexTimeCsvService
{
    public static function getAllYearly($year)
    {
        $flextimes = FlexTimeModel::whereBetween('start_date', [date($year . '/1/1'), date($year . '/12/31')])->get();
        $rows = [];
        $rows[] = ['신청날짜', '사원번호', '신청자', '결재자', '시작', '종료', '요일', '출근시간', '업무인수인계자'];
        foreach ($flextimes as $flextime) {
            $personcode = UserJoinService::getPersonCodeByUidSafe($flextime->uid);
            $name = UserJoinService::getNameByUidSafe($flextime->uid);
            $manager_uid_name = UserJoinService::getNameByUidSafe($flextime->manager_uid);
            $keeper_uid_name = UserJoinService::getNameByUidSafe($flextime->keeper_uid);

            $rows[] = [
                $flextime->start_date,
                $personcode,
                $name,
                $manager_uid_name,
                $flextime->start_date,
                $flextime->end_date,
                $flextime->weekdays,
                $flextime->start_time,
                $keeper_uid_name,
            ];
        }

        return $rows;
    }
}
