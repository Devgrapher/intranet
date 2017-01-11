<?php

namespace Intra\Controller;

use Intra\Service\FlexTime\FlexTimeService;
use Intra\Service\Holiday\UserHolidayPolicy;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserDtoHandler;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Intra\Lib\Response\CsvResponse;
use Symfony\Component\HttpFoundation\Response;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

use Intra\Model\FlexTimeModel;

class FlexTime implements ControllerProviderInterface
{
	public function connect(Application $app)
	{
		$controller_collection = $app['controllers_factory'];
		$controller_collection->get('/', [$this, 'index']);
		$controller_collection->get('/uid/{uid}', [$this, 'index']);
		$controller_collection->get('/uid/{uid}/year/{year}', [$this, 'index']);
		$controller_collection->post('uid/{uid}', [$this, 'add']);
		$controller_collection->put('uid/{uid}', [$this, 'edit']);
		$controller_collection->delete('uid/{uid}/{flextimeid}', [$this, 'del']);
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

		$user_flextime = FlexTimeModel::where('uid', $uid)->get();
		foreach ($user_flextime as $flextime) {
			$manager_dto_object = new UserDtoHandler(UserDtoFactory::createByUid($flextime->manager_uid));
			$manager_dto = $manager_dto_object->exportDto();
			$flextime->manager_uid_name = $manager_dto->name;

			$keeper_dto_object = new UserDtoHandler(UserDtoFactory::createByUid($flextime->keeper_uid));
			$keeper_dto = $keeper_dto_object->exportDto();
			$flextime->keeper_uid_name = $keeper_dto->name;
		}

		return $app['twig']->render('flextime/index.twig', [
			'uid' => $target_user_dto->uid,
			'name' => $target_user_dto->name,
			'flextimes' => $user_flextime,
			'year' => $year,
			'editable' => $editable,
			'availableUsers' => UserDtoFactory::createAvailableUserDtos(),
			'managerUsers' => UserDtoFactory::createManagerUserDtos()
		]);
	}

	public function add(Request $request)
	{
		try {
			$flextime = FlexTimeModel::create(array(
				'uid' => $request->get('uid'),
				'manager_uid' => $request->get('manager_uid'),
				'keeper_uid' => $request->get('keeper_uid'),
				'start_date' => $request->get('start_date'),
				'end_date' => $request->get('end_date'),
				'start_time' => $request->get('start_time'),
				'weekdays' => $request->get('weekdays'),
				'phone_emergency' => $request->get('phone_emergency'),
			));

			$flextimeService = new FlexTimeService();
			$flextimeService->sendAddMail($flextime);
		} catch (\Exception $e) {
			$ret = $e->getMessage();
			return new Response($ret);
		}

		return 0;
	}

	public function edit(Request $request)
	{
		try {
			$ret = 'error';

			$flextimeid = $request->get('flextimeid');
			$key = $request->get('key');
			$value = $request->get('value');

			$flextime = FlexTimeModel::find($flextimeid);
			if ($flextime) {
				$flextime->$key = $value;
				if ($flextime->save()) {
					$ret = $value;
					$flextimeService = new FlexTimeService();
					$flextimeService->sendEditMail($flextime);
				}

			}

			return $ret;

		} catch (\Exception $e) {
			$ret = $e->getMessage();
			return new Response($ret);
		}
	}

	public function del(Request $request)
	{
		try {
			$flextimeid = $request->get('flextimeid');
			$flextime = FlexTimeModel::find($flextimeid);
			if ($flextime) {
				if ($flextime->delete()) {
					$flextimeService = new FlexTimeService();
					$flextimeService->sendDelMail($flextime);
				}
			}

		} catch (\Exception $e) {
			$ret = $e->getMessage();
			return new Response($ret);
		}

		return 1;
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

		$flextimes = FlexTimeModel::whereBetween('start_date', [date($year . '/1/1'), date($year . '/12/31')])->get();
		$rows = [];
		$rows[] = ['신청날짜', '사원번호', '신청자', '결재자', '시작', '종료', '출근시간', '업무인수인계자', '비상시연락처'];
		foreach ($flextimes as $flextime) {
			$target_dto_object = new UserDtoHandler(UserDtoFactory::createByUid($flextime->uid));
			$target_dto = $target_dto_object->exportDto();

			$manager_dto_object = new UserDtoHandler(UserDtoFactory::createByUid($flextime->manager_uid));
			$manager_dto = $manager_dto_object->exportDto();

			$keeper_dto_object = new UserDtoHandler(UserDtoFactory::createByUid($flextime->keeper_uid));
			$keeper_dto = $keeper_dto_object->exportDto();

			$rows[] = [
				$flextime->start_date,
				$target_dto->personcode,
				$target_dto->name,
				$manager_dto->name,
				$flextime->start_date,
				$flextime->end_date,
				$flextime->start_time,
				$keeper_dto->name,
				$flextime->phone_emergency,
			];
		}

		return new CsvResponse($rows);
	}
}

