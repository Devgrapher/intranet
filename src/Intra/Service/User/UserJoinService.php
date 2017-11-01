<?php
namespace Intra\Service\User;

use Intra\Model\UserModel;
use Ridibooks\Platform\Common\DictsUtils;

class UserJoinService
{
    private static function getDtoByUidSafe($uid)
    {
        if (!UserModel::isExistByUid($uid)) {
            return null;
        }
        $dict = UserModel::getDictWithUid($uid);
        $dto = UserDto::importFromDatabase($dict);
        return $dto;
    }

    public static function getNameByUidSafe($uid)
    {
        $dto = self::getDtoByUidSafe($uid);
        if ($dto == null) {
            return null;
        }

        return $dto->name;
    }

    public static function getEmailByUidSafe($uid)
    {
        $dto = self::getDtoByUidSafe($uid);
        if ($dto == null) {
            return null;
        }

        return $dto->email;
    }

    public static function getPersonCodeByUidSafe($uid)
    {
        $dto = self::getDtoByUidSafe($uid);
        if ($dto == null) {
            return null;
        }

        return $dto->personcode;
    }

    public static function getTeamByUidSafe($uid)
    {
        $dto = self::getDtoByUidSafe($uid);
        if ($dto == null) {
            return null;
        }

        return $dto->team;
    }

    public static function getEmailsByTeam($team)
    {
        $dicts = UserModel::getDictsWithTeam($team);
        $ids = DictsUtils::extractValuesByKey($dicts, 'id');
        return array_map(
            function ($id) {
                return $id . '@' . $_ENV['domain'];
            },
            $ids
        );
    }
}
