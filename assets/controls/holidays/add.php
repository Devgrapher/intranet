<?php
/** @var $this Intra\Core\Control */
use Intra\Service\Holiday\UserHoliday;
use Intra\Service\Holiday\UserHolidayDto;
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
$yearly = $user_holiday->getYearly(strtotime($request->get('date')));
$holiday_raw = UserHolidayDto::importAddRequest($request, $yearly);

$db = IntraDb::getGnfDb();
$db->sqlBegin();
if ($holiday_ids = $user_holiday->add($holiday_raw)) {
    if ($user_holiday->sendNotification($holiday_ids, "휴가신청")) {
        if ($db->sqlEnd()) {
            return 1;
        }
    }
}

return 0;
