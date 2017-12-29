<?php
namespace Intra\Service\User;

use Intra\Core\MsgException;
use Intra\Lib\Azure\Settings;
use Intra\Model\SessionModel;
use Intra\Model\UserModel;

class UserSession
{
    /**
     * @var SessionModel
     */
    private static $session;

    public static function loginByAzure($id)
    {
        self::initStatic();

        if (!UserModel::isExistById($id)) {
            return false;
        } else {
            $user_dto_object = new UserDtoHandler(UserDtoFactory::importFromDatabaseWithId($id));
            if (!$user_dto_object->isValid()) {
                throw new MsgException(
                    '로그인 불가능한 계정입니다. 인사팀에 확인해주세요. <a href="https://login.windows.net/common/oauth2/logout?response_type=code&client_id=' . Settings::getClientId() . '&resource=https://graph.windows.net&redirect_uri=">로그인 계정을 여러개 쓰는경우 로그인 해제</a>하고 다시 시도해주세요'
                );
            }
        }

        $uid = UserModel::convertUidFromId($id);
        self::$session->set('users_uid', $uid);

        return true;
    }

    private static function initStatic()
    {
        self::$session = new SessionModel();
    }

    public static function logout()
    {
        self::initStatic();

        self::$session->set('users_uid', null);
    }

    /**
     * @return UserDto
     */
    public static function getSelfDto()
    {
        self::initStatic();

        if (!self::isLogined()) {
            return null;
        }
        if (self::$session->get('users_uid')) {
            $users_uid = self::$session->get('users_uid');

            return UserDtoFactory::createByUid($users_uid);
        }

        return null;
    }

    public static function isLogined()
    {
        self::initStatic();

        $users_uid = self::$session->get('users_uid');

        return intval($users_uid);
    }

    public static function isTa()
    {
        $user = self::getSelfDto();
        if ($user === null) {
            return false;
        }

        return UserPolicy::isTa($user);
    }

    public static function isPressManager()
    {
        $user = self::getSelfDto();
        if ($user === null) {
            return false;
        }

        return UserPolicy::isPressManager($user);
    }

    public static function isUserManager()
    {
        $user = self::getSelfDto();
        if ($user === null) {
            return false;
        }

        return UserPolicy::isUserManager($user);
    }
}
