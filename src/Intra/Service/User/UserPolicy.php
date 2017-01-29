<?php
namespace Intra\Service\User;

use Intra\Config\Config;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserPolicy
{
    public static function isFirstPageEditable(UserDto $self)
    {
        if ($self->is_admin || in_array($self->email, Config::$user_policy['first_page_editable'])) {
            return true;
        }
        return false;
    }

    public static function isHolidayEditable(UserDto $self)
    {
        if ($self->is_admin || in_array($self->email, Config::$user_policy['holiday_editable'])) {
            return true;
        }
        return false;
    }

    public static function isPressManager(UserDto $self)
    {
        if ($self->is_admin || in_array($self->email, Config::$user_policy['press_manager'])) {
            return true;
        }
        return false;
    }

    public static function isUserManager(UserDto $self)
    {
        if ($self->is_admin || in_array($self->email, Config::$user_policy['user_manager'])) {
            return true;
        }
        return false;
    }

    public static function isPostAdmin(UserDto $self)
    {
        if ($self->is_admin || in_array($self->email, Config::$user_policy['post_admin'])) {
            return true;
        }
        return false;
    }

    public static function isPaymentAdmin(UserDto $self)
    {
        if (in_array($self->email, Config::$user_policy['payment_admin'])) {
            return true;
        }
        return false;
    }

    public static function isSupportAdmin(UserDto $self, $target = 'all')
    {
        if (in_array($self->email, Config::$user_policy['support_admin']['all'])) {
            return true;
        }

        $target = strtolower($target);
        if ($target != 'all' && in_array($self->email, Config::$user_policy['support_admin'][$target])) {
            return true;
        }

        return false;
    }

    public static function isReceiptsAdmin(UserDto $self)
    {
        if ($self->is_admin || in_array($self->email, Config::$user_policy['receipts_admin'])) {
            return true;
        }
        return false;
    }

    public static function isTa(UserDto $user)
    {
        if (strpos($user->email, ".ta") !== false
            || strpos($user->email, ".oa") !== false
            || strpos(strtoupper($user->name), "TA") !== false
        ) {
            return true;
        }

        return false;
    }

    public static function assertRestrictedPath(Request $request)
    {
        $free_to_login_path = [
            '/usersession/login',
            '/usersession/login.azure',
            '/users/join',
            '/programs/insert',
            '/programs/list',
            '/api/ridibooks_ids',
            '/press/list'
        ];

        $is_free_to_login = in_array($request->getPathInfo(), $free_to_login_path);
        $uid = UserSession::isLogined();
        if (!$uid && Config::$is_dev) {
            UserSession::loginByAzure('test');
            $uid = UserSession::isLogined();
        }
        if (!$is_free_to_login && !$uid) {
            if ($request->isXmlHttpRequest()) {
                return new Response('login error');
            } else {
                return new RedirectResponse('/usersession/login');
            }
        }
        return null;
    }
}
