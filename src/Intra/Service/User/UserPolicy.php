<?php
namespace Intra\Service\User;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserPolicy
{
    private static function checkUserPolicy($email, $target)
    {
        if (empty($_ENV["user_policy_$target"])) {
            return false;
        }

        $policy_list = $_ENV["user_policy_$target"];
        if ($policy_list && in_array($email, explode(',', $policy_list))) {
            return true;
        }
    }

    public static function isFirstPageEditable(UserDto $self)
    {
        if ($self->is_admin || self::checkUserPolicy($self->email, 'first_page_editable')) {
            return true;
        }

        return false;
    }

    public static function isHolidayEditable(UserDto $self)
    {
        if ($self->is_admin || self::checkUserPolicy($self->email, 'holiday_editable')) {
            return true;
        }

        return false;
    }

    public static function isPressManager(UserDto $self)
    {
        if ($self->is_admin || self::checkUserPolicy($self->email, 'press_manager')) {
            return true;
        }

        return false;
    }

    public static function isUserManager(UserDto $self)
    {
        if ($self->is_admin || self::checkUserPolicy($self->email, 'user_manager')) {
            return true;
        }

        return false;
    }

    public static function isPostAdmin(UserDto $self)
    {
        if ($self->is_admin || self::checkUserPolicy($self->email, 'post_admin')) {
            return true;
        }

        return false;
    }

    public static function isPaymentAdmin(UserDto $self)
    {
        if ($self->is_admin || self::checkUserPolicy($self->email, 'payment_admin')) {
            return true;
        }

        return false;
    }

    public static function isSupportAdmin(UserDto $self, $target = 'all')
    {
        if ($self->is_admin || self::checkUserPolicy($self->email, 'support_admin_all')) {
            return true;
        }

        $target = strtolower($target);
        if ($target != 'all' && self::checkUserPolicy($self->email, "support_admin_$target")) {
            return true;
        }

        return false;
    }

    public static function isReceiptsAdmin(UserDto $self)
    {
        if ($self->is_admin || self::checkUserPolicy($self->email, 'receipts_admin')) {
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


    public static function isStudioD(UserDto $user)
    {
        if ($user->email === "studiod@ridi.com") {
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
        if (!$uid && $_ENV['is_dev']) {
            UserSession::loginByAzure($_ENV['test_id']);
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
