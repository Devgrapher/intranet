<?php
/** @var $this Intra\Core\Control */

use Intra\Service\Holiday\UserHoliday;
use Intra\Service\Holiday\UserHolidayPolicy;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Ridibooks\Platform\Common\CsvResponse;
use Symfony\Component\HttpFoundation\Response;

$request = $this->getRequest();

if (!UserPolicy::isHolidayEditable(UserSession::getSelfDto())) {
    return new Response("권한이 없습니다", 403);
}

$year = $request->get('year');
if (!intval($year)) {
    $year = date('Y');
}

$rows = [
    ['연도', '사원번호', '이름', '입사일자', '퇴사일자', '연차부여', '사용일수', '잔여일수']
];

$users = UserDtoFactory::createAllUserDtos();

foreach ($users as $user) {
    $user_holiday = new UserHoliday($user);
    $user_holiday_policy = new UserHolidayPolicy($user);

    $joinYear = $user_holiday->getYearByYearly(1);
    $yearly = $year - $joinYear + 1;

    $fullCost = $user_holiday_policy->getAvailableCost($yearly);
    $usedCost = $user_holiday_policy->getUsedCost($yearly);
    $remainCost = $fullCost - $usedCost;

    $rows[] = [
        $year,
        $user->personcode,
        $user->name,
        $user->on_date,
        $user->off_date,
        $fullCost,
        $usedCost,
        $remainCost
    ];
}

return new CsvResponse($rows);
