<?php

namespace Intra\Controller;

use Intra\Model\HolidayModel;
use Intra\Service\File\UserImageFileService;
use Intra\Service\User\Organization;
use Intra\Service\User\UserDto;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserDtoHandler;
use Intra\Service\User\UserEditService;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Intra\Service\User\UserType;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UsersController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];
        $controller_collection->get('/', [$this, 'index']);
        $controller_collection->get('/list', [$this, 'getList']);
        $controller_collection->get('/me', [$this, 'getMe']);
        $controller_collection->post('/edit', [$this, 'edit']);
        $controller_collection->post('/image_upload', [$this, 'uploadImage']);
        $controller_collection->post('/{userid}/updateExtra/{key}/{value}', [$this, 'updateExtraAjax']);
        $controller_collection->get('/jeditable_key/{key}', [$this, 'jeditableKey']);

        return $controller_collection;
    }

    public function index(Application $app)
    {
        $self = UserSession::getSelfDto();
        $replaceable = UserPolicy::isUserSpotEditable($self);

        $holiday_model = new HolidayModel();
        $today_holidays = $holiday_model->getsToday();
        $users = UserDtoFactory::createAvailableUserDtos();

        $file_service = new UserImageFileService();
        $users_ret = [];
        foreach ($users as $user) {
            $user_arr = [];
            $user_arr['uid'] = $user->uid;
            $user_arr['name'] = $user->name;
            $user_arr['image'] = $user->image ? $file_service->convertPathToS3Url($user->image) : $user->image;
            $user_arr['team'] = $user->team;
            $user_arr['email'] = $user->email;
            $user_arr['inner_call'] = $user->inner_call;
            $user_arr['mobile'] = $user->mobile;
            $user_arr['position'] = $user->position;
            $user_arr['birth'] = $user->birth;
            $user_arr['extra'] = $user->extra;

            $timezone = 'Asia/Seoul';
            $now = new \DateTime();

            foreach ($today_holidays as $holiday) {
                if ($user->uid == $holiday->uid) {
                    if ($holiday->type == '오전반차' || $holiday->type == '무급오전반차') {
                        $dateTime = new \DateTime('15:00', new \DateTimeZone($timezone));
                        if ($dateTime->diff($now)->format('%R') === '-') {
                            $user_arr['absence'] = true;
                            $user_arr['state'] = 'morning-off';
                        }
                    } elseif ($holiday->type == '오후반차' || $holiday->type == '무급오후반차') {
                        $dateTime = new \DateTime('14:00', new \DateTimeZone($timezone));
                        if ($dateTime->diff($now)->format('%R') === '+') {
                            $user_arr['absence'] = true;
                        }
                        $user_arr['state'] = 'afternoon-off';
                    } elseif ($holiday->type != 'PWT') {
                        $user_arr['absence'] = true;
                        $user_arr['state'] = 'day-off';
                    }
                }
            }

            $users_ret[] = $user_arr;
        }

        return $app['twig']->render('users/index.twig', [
            'replaceable' => $replaceable,
            'users' => $users_ret
        ]);
    }

    public function getList(Request $request, Application $app)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isUserManager($self) && !UserPolicy::isPolicyRecipientEditable($self)) {
            return Response::create('권한이 없습니다.', Response::HTTP_UNAUTHORIZED);
        }

        $user_dtos = UserDtoFactory::createAllUserDtos();
        if ($request->get('outer')) {
            $user_dtos = array_values(array_filter($user_dtos, function (UserDto $item) {
                $type = (new UserDtoHandler($item))->getType();

                return $type == UserType::OUTER;
            }));
        } else {
            $user_dtos = array_values(array_filter($user_dtos, function (UserDto $item) {
                $type = (new UserDtoHandler($item))->getType();

                return $type != UserType::OUTER;
            }));
        }

        return $app->json($user_dtos);
    }

    public function getMe(Request $request, Application $app)
    {
        if (!in_array('application/json', $request->getAcceptableContentTypes())) {
            return $app['twig']->render('users/me.twig');
        }

        $dto = UserSession::getSelfDto();

        $service = new UserImageFileService();
        $image_location = $service->getLastFileLocation($dto->uid);
        $dto->image = $image_location ? $image_location : 'https://placehold.it/300x300';

        return JsonResponse::create($dto);
    }

    public function edit(Request $request)
    {
        $uid = $request->get('pk');
        $name = $request->get('name');
        $value = $request->get('value');

        if (UserEditService::updateInfo($uid, $name, $value) !== null) {
            return Response::create($value, Response::HTTP_OK);
        } else {
            return Response::create("server error", Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }

    public function uploadImage(Request $request)
    {
        /* @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('files')[0];
        $self = UserSession::getSelfDto();
        if (!$self) {
            return JsonResponse::create('unknown user', JsonResponse::HTTP_UNAUTHORIZED);
        }

        $uid = $request->get('uid');
        if (!$uid) {
            return JsonResponse::create('no uid', JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $service = new UserImageFileService();
            $img_thumb = $service->createThumbFromFile($uploadedFile->getRealPath());
            $new_file = $service->uploadFile(
                $uid,
                $uid,
                $uid . '.jpg',
                $img_thumb,
                'image/jpg'
            );
            UserEditService::updateInfo($uid, 'image', $new_file['location']);

            return JsonResponse::create($service->getLastFileLocation($uid));
        } catch (\Exception $e) {
            return new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, $e->getMessage());
        }
    }

    public function updateExtraAjax(Request $request)
    {
        $uid = $request->get('userid');
        $key = $request->get('key');
        $value = rawurldecode($request->get('value'));
        if ($value === 'true') {
            $value = true;
        } elseif ($value === 'false') {
            $value = false;
        }

        $user = new UserDtoHandler(UserDtoFactory::createByUid($uid));
        $user->setExtra($key, $value);

        return Response::create('success', Response::HTTP_OK);
    }

    public function jeditableKey(Request $request)
    {
        $key = $request->get('key');
        $dicts = [];
        if ($key == 'team') {
            $values = Organization::readTeamNames();
            foreach ($values as $value) {
                $dicts[$value] = $value;
            }
        }

        return JsonResponse::create($dicts);
    }
}
