<?php

namespace Intra\Service\User;

use Exception;
use Intra\Model\UserModel;

class UserDtoFactory
{
    /**
     * @param $uid
     *
     * @return UserDto
     * @throws Exception
     */
    public static function createByUid($uid)
    {
        if (!UserModel::isExistByUid($uid)) {
            UserSession::logout();
            throw new Exception("Database Row Not Exist");
        }
        $dict = UserModel::getDictWithUid($uid);

        return UserDto::importFromDatabase($dict);
    }

    public static function createFromDatabaseDicts($dicts)
    {
        $return = [];
        foreach ($dicts as $dict) {
            $return[] = UserDto::importFromDatabase($dict);
        }

        return $return;
    }

    /**
     * @return UserDto[]
     */
    public static function createAvailableUserDtos()
    {
        $dicts = UserModel::getDictsAvailable();

        return self::createFromDatabaseDicts($dicts);
    }

    /**
     * @return UserDto[]
     */
    public static function createAllUserDtos()
    {
        $dicts = UserModel::getAllDicts();

        return self::createFromDatabaseDicts($dicts);
    }

    /**
     * @return UserDto[]
     */
    public static function createTeamUserDtos($team_name)
    {
        $dicts = UserModel::getDictsWithTeam($team_name);

        return self::createFromDatabaseDicts($dicts);
    }

    /**
     * @return UserDto[]
     */
    public static function createManagerUserDtos()
    {
        $dicts = UserModel::getDictsOfManager();

        return self::createFromDatabaseDicts($dicts);
    }

    /**
     * @param $uids
     *
     * @return UserDto[]
     */
    public static function createDtosByUid($uids)
    {
        $return = [];
        $dicts = UserModel::getDictWithUids($uids);
        foreach ($dicts as $dict) {
            $return[] = UserDto::importFromDatabase($dict);
        }

        return $return;
    }

    /**
     * @param $id
     *
     * @return UserDto
     * @throws Exception
     */
    public static function importFromDatabaseWithId($id)
    {
        $uid = UserModel::convertUidFromId($id);

        return self::createByUid($uid);
    }
}
