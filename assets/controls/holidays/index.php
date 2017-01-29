<?php
/** @var $this Intra\Core\Control */
use Intra\Model\HolidayModel;
use Intra\Service\Holiday\UserHoliday;
use Intra\Service\Holiday\UserHolidayPolicy;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserDtoHandler;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;

$request = $this->getRequest();
$self = UserSession::getSelfDto();

$uid = $request->get('uid');
if (!intval($uid)) {
    $uid = $self->uid;
}
$year = $request->get('year');
if (!intval($year)) {
    $year = date('Y');
}

$is_holiday_master = UserPolicy::isHolidayEditable($self);
$editable = $is_holiday_master;
if (!$is_holiday_master) {
    if ($uid != $self->uid) {
        $uid = $self->uid;
    }
}

$user_dto_object = new UserDtoHandler(UserDtoFactory::createByUid($uid));
$target_user_dto = $user_dto_object->exportDto();
$user_holiday = new UserHoliday($target_user_dto);
$user_holiday_policy = new UserHolidayPolicy($target_user_dto);

$joinYear = $user_holiday->getYearByYearly(0);
$yearly = $year - $joinYear;

//main
{
    $today = date('Y-m-d');
    $holidayConst = HolidayModel::$const;
    $yearPrev = $year - 1;
    $yearNext = $year + 1;
    $yearlyFrom = date('Y-m-d', $user_holiday_policy->getYearlyBeginTimestamp($yearly));
    $yearlyTo = date('Y-m-d', $user_holiday_policy->getYearlyEndTimestamp($yearly));

    $fullCost = $user_holiday_policy->getAvailableCost($yearly);
    $usedCost = $user_holiday_policy->getUsedCost($yearly);
    $modCost = $user_holiday_policy->getModCost($year);
    $remainCost = $user_holiday_policy->getRemainCost($yearly);
    $holidays = $user_holiday->getUserHolidays($yearly);
    $holidayInfo = $user_holiday_policy->getDetailInfomationByYearly($yearly);

    $availableUsers = UserDtoFactory::createAvailableUserDtos();
    $managerUsers = UserDtoFactory::createManagerUserDtos();
}

return [
    'target_user_dto' => $target_user_dto,
    'today' => $today,
    'holidays' => $holidays,
    'year' => $year,
    'yearly' => $yearly,
    'yearPrev' => $yearPrev,
    'yearNext' => $yearNext,
    'yearlyFrom' => $yearlyFrom,
    'yearlyTo' => $yearlyTo,
    'fullCost' => $fullCost,
    'modCost' => $modCost,
    'remainCost' => $remainCost,
    'editable' => $editable,
    'self' => $self,
    'availableUsers' => $availableUsers,
    'holidayConst' => $holidayConst,
    'holidayInfo' => $holidayInfo,
    'managerUsers' => $managerUsers
];
