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

$holidayid = $request->get('holidayid');
$key = $request->get('key');
$value = $request->get('value');

$user_holiday = new UserHoliday($dto);

$db = IntraDb::getGnfDb();
$db->sqlBegin();
$ret = $user_holiday->edit($holidayid, $key, $value);
if ($user_holiday->sendNotification([$holidayid], "휴가수정")) {
    if ($db->sqlEnd()) {
        return $ret;
    }
}

return 'error';
