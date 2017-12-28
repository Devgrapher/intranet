<?php

namespace Intra\Controller;

use Intra\Core\MsgException;
use Intra\Lib\Azure\AuthorizationHelperForAADGraphService;
use Intra\Lib\Azure\GraphServiceAccessHelper;
use Intra\Model\UserModel;
use Intra\Service\User\UserDto;
use Intra\Service\User\UserMailService;
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

        try {
            if (!UserSession::loginByAzure($user->mailNickname)) {
                $join_dto = UserDto::importFromDatabase([
                    'id' => $user->mailNickname,
                    'name' => $user->displayName,
                    'email' => $user->mail,
                    'on_date' => date('Y-m-d'),
                    'off_date' => '9999-01-01',
                ]);
                $join_dto->extra = null;
                UserModel::addUser($join_dto);
                UserMailService::sendMail('인트라넷 회원가입', $join_dto, $app);
            }

            return new RedirectResponse('/?after_login');
        } catch (MsgException $e) {
            return Response::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function login(Request $request, Application $app)
    {
        $azure_login = AuthorizationHelperForAADGraphService::getAuthorizatonURL();
        if ($_ENV['INTRA_DEBUG']) {
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
