<?php

namespace Intra\Controller;

use Intra\Core\MsgException;
use Intra\Lib\Azure\AuthorizationHelperForAADGraphService;
use Intra\Lib\Azure\GraphServiceAccessHelper;
use Intra\Service\User\UserSession;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserSessionController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];
        $controller_collection->get('/login.azure', [$this, 'loginAzure']);
        $controller_collection->get('/login', [$this, 'login']);
        $controller_collection->get('/logout', [$this, 'logout']);
        return $controller_collection;
    }

    public function loginAzure(Request $request, Application $app)
    {
        $code = $request->get('code');
        $azure_login_token_array = AuthorizationHelperForAADGraphService::getAuthenticationHeaderFor3LeggedFlow($code);
        $user = GraphServiceAccessHelper::getMeEntry($azure_login_token_array);
        $id = $user->mailNickname;

        try {
            if (UserSession::loginByAzure($id)) {
                return new RedirectResponse('/?after_login');
            } else {
                return new RedirectResponse('/users/join');
            }
        } catch (MsgException $e) {
            return Response::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function login(Request $request, Application $app)
    {
        $azure_login = AuthorizationHelperForAADGraphService::getAuthorizatonURL();
        if ($_ENV['is_dev']) {
            $azure_login = '/';
        }

        return $app['twig']->render('usersession/login.twig', [
            'azure_login' => $azure_login
        ]);
    }

    public function logout(Request $request, Application $app)
    {
        UserSession::logout();
        return new RedirectResponse('/usersession/login');
    }
}
