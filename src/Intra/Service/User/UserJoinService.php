<?php
namespace Intra\Service\User;

use Intra\Config\Config;
use Intra\Core\MsgException;
use Intra\Lib\Azure\Settings;
use Intra\Model\UserModel;
use Ridibooks\Platform\Common\DictsUtils;

class UserJoinService
{
    /**
     * @param $id
     *
     * @throws MsgException
     */
    public static function assertUserIdExist($id)
    {
        if (!UserModel::isExistById($id)) {
            throw new MsgException(
                '아이디가 없습니다. <a href="/users/join">가입</a>을 해주시거나 <a href="https://login.windows.net/common/oauth2/logout?response_type=code&client_id=' . Settings::getClientId() . '&resource=https://graph.windows.net&redirect_uri=">로그인 계정을 여러개 쓰는경우 로그인 해제</a>하고 다시 시도해주세요'
            );
        }

        $user_dto_object = new UserDtoHandler(UserDtoFactory::importFromDatabaseWithId($id));

        if (!$user_dto_object->isValid()) {
            throw new MsgException(
                '로그인 불가능한 계정입니다. 인사팀에 확인해주세요. <a href="https://login.windows.net/common/oauth2/logout?response_type=code&client_id=' . Settings::getClientId() . '&resource=https://graph.windows.net&redirect_uri=">로그인 계정을 여러개 쓰는경우 로그인 해제</a>하고 다시 시도해주세요'
            );
        }
    }

    public static function join($request)
    {
        $join_dto = UserDto::importFromJoinRequest($request);
        self::assertJoin($join_dto);
        UserModel::addUser($join_dto);
    }

    private static function assertJoin($join_dto)
    {
        if (!preg_match("/^.+$/", $join_dto->name)) {
            throw new MsgException('이름을 입력해주세요');
        }
        if (!preg_match("/^.+@.+\..+$/", $join_dto->email)) {
            throw new MsgException('올바른 이메일을 입력해주세요');
        }
        if (!preg_match("/^[\w_\.]+$/", $join_dto->id)) {
            throw new MsgException('아이디는 영문과 숫자, 그리고 _(언더바)와 .(점)만 가능합니다');
        }
        if (!preg_match("/^.+$/", $join_dto->id)) {
            throw new MsgException('아이디를 입력해주세요');
        }
        if (!preg_match("/^\d+\/\d+\/\d+$/", $join_dto->birth) || $join_dto->birth == '0000/00/00') {
            throw new MsgException('생년월일을 올바르게 입력해주세요');
        }
        if (!preg_match("/^\d+-\d+-\d+$/", $join_dto->mobile) || $join_dto->mobile == '010-0000-0000') {
            throw new MsgException('전화번호를 올바르게 입력해주세요');
        }

        if (UserModel::isExistById($join_dto->id)) {
            throw new MsgException('이미 존재하는 계정입니다');
        }
    }

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

    public static function getEmailsByTeam($team)
    {
        $dicts = UserModel::getDictsWithTeam($team);
        $ids = DictsUtils::extractValuesByKey($dicts, 'id');
        return array_map(
            function ($id) {
                return $id . '@' . Config::$domain;
            },
            $ids
        );
    }
}
