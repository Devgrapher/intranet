<?php
declare(strict_types=1);

namespace Intra\Controller\Admin;

use Intra\Service\File\UserImageFileService;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];

        $controller_collection->get('/', [$this, 'get']);
        $controller_collection->get('/{uid}/image', [$this, 'getImage']);

        $controller_collection->before(function () {
            if (!UserPolicy::isUserManager(UserSession::getSelfDto())) {
                return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
            }
        });

        return $controller_collection;
    }

    public function get(Request $request, Application $app)
    {
        return $app['twig']->render('admin/user/index.twig');
    }

    public function getImage(Request $request, Application $app)
    {
        $uid = $request->get('uid');
        if (!$uid || $uid === 'me') {
            $dto = UserSession::getSelfDto();
        } else {
            $dto = UserDtoFactory::createByUid($uid);
        }

        $service = new UserImageFileService();
        $image_location = $service->getLastFileLocation((string)$dto->uid);
        $dto->image = $image_location ? $image_location : 'https://placehold.it/300x300';

        $users = UserDtoFactory::createAvailableUserDtos();

        return $app['twig']->render('admin/user/image.twig', [
            'uid' => $dto->uid,
            'name' => $dto->name,
            'image' => $dto->image ? $dto->image : null,
            'users' => $users,
        ]);
    }
}
