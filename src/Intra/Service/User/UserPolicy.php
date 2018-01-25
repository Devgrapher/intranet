<?php
namespace Intra\Service\User;

use Intra\Model\PolicyModel;
use Intra\Model\UserEloquentModel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserPolicy
{
    const POLICY_RECIPIENT_EDITTABLE = 'policy_recipient_manager';
    const USER_SPOT_EDITABLE = 'user_spot_editable';
    const HOLIDAY_EDITABLE = 'holiday_editable';
    const PRESS_MANAGER = 'press_manager';
    const USER_MANAGER = 'user_manager';
    const POST_ADMIN = 'post_admin';
    const PAYMENT_ADMIN = 'payment_admin';
    const SUPPORT_ADMIN_ALL = 'support_all_admin';
    const SUPPORT_ADMIN_DEVICE = 'support_device_admin';
    const SUPPORT_ADMIN_FAMILY_EVENT = 'support_familyevent_admin';
    const SUPPORT_ADMIN_BUSINESS_CARD = 'support_businesscard_admin';
    const SUPPORT_ADMIN_DEPOT = 'support_depot_admin';
    const SUPPORT_ADMIN_GIFT_CARD_PURCHASE = 'support_giftcard_purchase_admin';
    const SUPPORT_ADMIN_TRAINING = 'support_training_admin';
    const SUPPORT_ADMIN_VPN = 'support_vpn_admin';
    const SUPPORT_ADMIN_USB = 'support_usb_admin';
    const RECEIPTS_ADMIN = 'receipts_admin';
    const TA = 'ta';
    const GUEST = 'guest';

    public static function getAllWithUsers(): array
    {
        $policies = PolicyModel::all();
        $roles = $policies->toArray();

        $assigned = [];
        foreach ($policies as $policy) {
            $assigned[$policy['keyword']] = $policy->users->pluck('uid')->all();
        }

        return [
            'roles' => $roles,
            'assigned' => $assigned,
        ];
    }

    public static function setAll(array $assigned)
    {
        foreach ($assigned as $keyword => $uids) {
            self::setPolicy($keyword, $uids);
        }
    }

    public static function setPolicy(string $keyword, array $uids)
    {
        PolicyModel::where('keyword', $keyword)->first()->users()->sync($uids);
    }

    private static function checkPermission(UserDto $self, array $roles): bool
    {
        $policies = UserEloquentModel::find($self->uid)->policies()->get();
        $results = $policies->whereIn('keyword', $roles)->all();

        return count($results) > 0;
    }

    public static function isSuperAdmin(UserDto $self): bool
    {
        return $self->is_admin === 1;
    }

    public static function isPolicyRecipientEditable(UserDto $self): bool
    {
        if (self::isSuperAdmin($self)) {
            return true;
        }

        return self::checkPermission($self, [self::POLICY_RECIPIENT_EDITTABLE]);
    }

    public static function isUserSpotEditable(UserDto $self): bool
    {
        if (self::isSuperAdmin($self)) {
            return true;
        }

        return self::checkPermission($self, [self::USER_SPOT_EDITABLE]);
    }

    public static function isHolidayEditable(UserDto $self): bool
    {
        if (self::isSuperAdmin($self)) {
            return true;
        }

        return self::checkPermission($self, [self::HOLIDAY_EDITABLE]);
    }

    public static function isPressManager(UserDto $self): bool
    {
        if (self::isSuperAdmin($self)) {
            return true;
        }

        return self::checkPermission($self, [self::PRESS_MANAGER]);
    }

    public static function isUserManager(UserDto $self): bool
    {
        if (self::isSuperAdmin($self)) {
            return true;
        }

        return self::checkPermission($self, [self::USER_MANAGER]);
    }

    public static function isTeamManager(UserDto $self): bool
    {
        return $self->position === '팀장';
    }

    public static function isPostAdmin(UserDto $self): bool
    {
        if (self::isSuperAdmin($self)) {
            return true;
        }

        return self::checkPermission($self, [self::POST_ADMIN]);
    }

    public static function isPaymentAdmin(UserDto $self): bool
    {
        if (self::isSuperAdmin($self)) {
            return true;
        }

        return self::checkPermission($self, [self::PAYMENT_ADMIN]);
    }

    public static function isSupportAdmin(UserDto $self, string $target = 'all'): bool
    {
        if (self::isSuperAdmin($self)) {
            return true;
        }

        return self::checkPermission($self, [
            self::SUPPORT_ADMIN_ALL,
            self::getSupportPolicyName($target),
        ]);
    }

    public static function getSupportPolicyName($target)
    {
        return 'support_' . strtolower($target) . '_admin';
    }

    public static function isReceiptsAdmin(UserDto $self): bool
    {
        if (self::isSuperAdmin($self)) {
            return true;
        }

        return self::checkPermission($self, [self::RECEIPTS_ADMIN]);
    }

    public static function isTa(UserDto $self): bool
    {
        return self::checkPermission($self, [self::TA]);
    }

    public static function isStudioD(UserDto $self): bool
    {
        return self::checkPermission($self, [self::GUEST]);
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
        if (!$uid && $_ENV['INTRA_TEST_ID'] && $_ENV['INTRA_DEBUG']) {
            UserSession::loginByAzure($_ENV['INTRA_TEST_ID']);
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
