<?php

/** @var $this Intra\Core\Control */
use Intra\Service\Holiday\UserHoliday;
use Intra\Service\IntraDb;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;

$request = $this->getRequest();

if (UserPolicy::isHolidayEditable(UserSession::getSelfDto())) {
    $uid = $request->get('uid');
    $dto = UserDtoFactory::createByUid($uid);
} else {
    $dto = UserSession::getSelfDto();
}

$user_holiday = new UserHoliday($dto);
$holidayid = $request->get('holidayid');

//finalize
$db = IntraDb::getGnfDb();
$db->sqlBegin();
if ($user_holiday->del($holidayid)) {
    if ($user_holiday->sendNotification([$holidayid], '휴가취소')) {
        if ($db->sqlEnd()) {
            return 1;
        }
    }
}

return 0;
