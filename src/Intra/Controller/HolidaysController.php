<?php

namespace Intra\Controller;

use Intra\Model\HolidayModel;
use Intra\Service\Holiday\UserHoliday;
use Intra\Service\Holiday\UserHolidayDto;
use Intra\Service\Holiday\UserHolidayPolicy;
use Intra\Service\Holiday\UserHolidayStat;
use Intra\Service\IntraDb;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserDtoHandler;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Ridibooks\Platform\Common\CsvResponse;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HolidaysController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];
        $controller_collection->get('/', [$this, 'index']);
        $controller_collection->get('/uid/{uid}', [$this, 'index']);
        $controller_collection->get('/uid/{uid}/year/{year}', [$this, 'index']);
        $controller_collection->post('uid/{uid}', [$this, 'add']);
        $controller_collection->put('uid/{uid}', [$this, 'edit']);
        $controller_collection->delete('uid/{uid}/{holidayid}', [$this, 'del']);
        $controller_collection->get('/download/{year}', [$this, 'download']);
        $controller_collection->get('/downloadRemain/{year}', [$this, 'downloadRemain']);
        return $controller_collection;
    }

    public function index(Request $request, Application $app)
    {
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
            $modList = $user_holiday_policy->getModList($year);
            $remainCost = $user_holiday_policy->getRemainCost($yearly);
            $holidays = $user_holiday->getUserHolidays($yearly);
            $holidayInfo = $user_holiday_policy->getDetailInfomationByYearly($yearly);

            $availableUsers = UserDtoFactory::createAvailableUserDtos();
            $managerUsers = UserDtoFactory::createManagerUserDtos();
        }

        return $app['twig']->render('holidays/index.twig', [
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
            'modList' => $modList,
            'remainCost' => $remainCost,
            'editable' => $editable,
            'self' => $self,
            'availableUsers' => $availableUsers,
            'holidayConst' => $holidayConst,
            'holidayInfo' => $holidayInfo,
            'managerUsers' => $managerUsers
        ]);
    }

    public function add(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            $ret = $e->getMessage();
            return new Response($ret);
        }

        return 0;
    }

    public function edit(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            $ret = $e->getMessage();
            return new Response($ret);
        }

        return 'error';
    }

    public function del(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            $ret = $e->getMessage();
            return new Response($ret);
        }

        return 0;
    }

    public function download(Request $request)
    {
        if (!UserPolicy::isHolidayEditable(UserSession::getSelfDto())) {
            return new Response("권한이 없습니다", 403);
        }

//input
        $year = $request->get('year');
        if (!intval($year)) {
            $year = date('Y');
        }

        $user_holiday = new UserHolidayStat(UserSession::getSelfDto());
        $holidays = $user_holiday->getHolidaysAllUsers($year);

        $csvs = [
            '신청날짜' => 'request_date',
            '사원번호' => 'personcode',
            '신청자' => 'uid_name',
            '결재자' => 'manager_uid_name',
            '종류' => 'type',
            '사용날짜' => 'date',
            '소모연차' => 'cost',
            '업무인수인계자' => 'keeper_uid_name',
            '비상시연락처' => 'phone_emergency',
            '비고' => 'memo',
        ];
        $rows = [];
        $rows[] = array_keys($csvs);
        foreach ($holidays as $holiday) {
            $row = [];
            foreach ($csvs as $key) {
                $row[] = $holiday->$key;
            }
            $rows[] = $row;
        }

        return new CsvResponse($rows);
    }

    public function downloadRemain(Request $request)
    {
        if (!UserPolicy::isHolidayEditable(UserSession::getSelfDto())) {
            return new Response("권한이 없습니다", 403);
        }

        $year = $request->get('year');
        if (!intval($year)) {
            $year = date('Y');
        }

        $rows = [
            ['연도', '사원번호', '이름', '입사일자', '퇴사일자', '연차부여', '사용일수', '조정일수', '잔여일수']
        ];

        $users = UserDtoFactory::createAllUserDtos();

        foreach ($users as $user) {
            $user_holiday = new UserHoliday($user);
            $user_holiday_policy = new UserHolidayPolicy($user);

            $joinYear = $user_holiday->getYearByYearly(1);
            $yearly = $year - $joinYear + 1;

            $fullCost = $user_holiday_policy->getAvailableCost($yearly);
            $usedCost = $user_holiday_policy->getUsedCost($yearly);
            $modCost = $user_holiday_policy->getModCost($year);
            $remainCost = $fullCost - $usedCost + $modCost;

            $rows[] = [
                $year,
                $user->personcode,
                $user->name,
                $user->on_date,
                $user->off_date,
                $fullCost,
                $usedCost,
                $modCost,
                $remainCost
            ];
        }

        return new CsvResponse($rows);
    }
}
