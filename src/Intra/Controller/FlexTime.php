<?php

namespace Intra\Controller;

use Intra\Model\FlexTimeModel;
use Intra\Service\FlexTime\FlexTimeCsvService;
use Intra\Service\FlexTime\FlexTimeMailService;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserDtoHandler;
use Intra\Service\User\UserJoinService;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Ridibooks\Platform\Common\CsvResponse;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
            $flextime->manager_uid_name = UserJoinService::getNameByUidSafe($flextime->manager_uid);
            $flextime->keeper_uid_name = UserJoinService::getNameByUidSafe($flextime->keeper_uid);
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
            $weekdays = $request->get('weekdays');
            if ($weekdays) {
                $weekdays = implode(',', $request->get('weekdays'));
            } else {
                $weekdays = '월,화,수,목,금';
            }

            $flextime = FlexTimeModel::create([
                'uid' => $request->get('uid'),
                'manager_uid' => $request->get('manager_uid'),
                'keeper_uid' => $request->get('keeper_uid'),
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date'),
                'start_time' => $request->get('start_time'),
                'weekdays' => $weekdays,
            ]);

            FlexTimeMailService::sendMail($flextime, '추가');
        } catch (\Exception $e) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }

        return Response::create('success', Response::HTTP_OK);
    }

    public function edit(Request $request)
    {
        try {
            $flextimeid = $request->get('flextimeid');
            $key = $request->get('key');
            $value = $request->get('value');

            $flextime = FlexTimeModel::find($flextimeid);
            if ($flextime) {
                $flextime->$key = $value;
                if ($flextime->save()) {
                    FlexTimeMailService::sendMail($flextime, '변경');
                }
            }
            return Response::create($value, Response::HTTP_OK);
        } catch (\Exception $e) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    public function del(Request $request)
    {
        try {
            $flextimeid = $request->get('flextimeid');
            $flextime = FlexTimeModel::find($flextimeid);
            if ($flextime) {
                if ($flextime->delete()) {
                    FlexTimeMailService::sendMail($flextime, '삭제');
                }
            }
        } catch (\Exception $e) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }

        return Response::create('success', Response::HTTP_OK);
    }

    public function download(Request $request)
    {
        if (!UserPolicy::isHolidayEditable(UserSession::getSelfDto())) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $year = $request->get('year');
        if (!intval($year)) {
            $year = date('Y');
        }

        $csvRows = FlexTimeCsvService::getAllYearly($year);
        return CsvResponse::create($csvRows);
    }
}
