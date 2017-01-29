<?php

namespace Intra\Controller;

use Intra\Model\HolidayAdjustModel;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HolidayAdmin implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];
        $controller_collection->get('/', [$this, 'index']);
        $controller_collection->get('/list', [$this, 'getList']);
        $controller_collection->get('/uid/{uid}', [$this, 'get']);
        $controller_collection->get('/uid/{uid}/year/{year}', [$this, 'get']);
        $controller_collection->post('uid/{uid}', [$this, 'add']);
        $controller_collection->delete('uid/{uid}/id/{id}', [$this, 'del']);
        return $controller_collection;
    }

    public function index(Request $request, Application $app)
    {
        if (!UserPolicy::isHolidayEditable(UserSession::getSelfDto())) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        return $app['twig']->render('holidays/admin.twig', []);
    }

    public function get(Request $request, Application $app)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isHolidayEditable($self)) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $uid = $request->get('uid');
        if (!intval($uid)) {
            $uid = $self->uid;
        }
        $year = $request->get('year');
        if (!intval($year)) {
            $year = date('Y');
        }

        $mods = HolidayAdjustModel::where('uid', $uid)->where('diff_year', $year)->get();
        return JsonResponse::create($mods, Response::HTTP_OK);
    }

    public function getList(Request $request, Application $app)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isHolidayEditable($self)) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $userList = UserDtoFactory::createAvailableUserDtos();
        $managerList = UserDtoFactory::createManagerUserDtos();
        return JsonResponse::create([
            'userList' => $userList,
            'managerList' => $managerList,
        ], Response::HTTP_OK);
    }

    public function add(Request $request)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isHolidayEditable($self)) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $uid = $data['uid'];
        if (!intval($uid)) {
            $uid = $self->uid;
        }
        $year = $data['diffYear'];
        if (!intval($year)) {
            $year = date('Y');
        }

        $new = HolidayAdjustModel::create([
            'uid' => $uid,
            'diff_year' => $year,
            'manager_uid' => $data['managerUid'],
            'diff' => $data['diff'],
            'reason' => $data['reason'],
        ]);
        return JsonResponse::create($new, Response::HTTP_CREATED);
    }

    public function del(Request $request)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isHolidayEditable($self)) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $id = $request->get('id');
        $flextime = HolidayAdjustModel::find($id);
        if ($flextime) {
            if ($flextime->delete()) {
                return Response::create('ok', Response::HTTP_OK);
            }
        }

        return Response::create('error', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
