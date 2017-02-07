<?php
namespace Intra\Model;

use Intra\Core\BaseModel;
use Intra\Core\MsgException;
use Intra\Model\Base\ClassLightFunctionCache;
use Intra\Service\IntraDb;
use Intra\Service\User\UserDto;

class UserModel extends BaseModel
{
    use ClassLightFunctionCache;

    private static $table = 'users';

    /**
     * @param $userJoinDto UserDto
     *
     * @return bool
     * @throws MsgException
     */
    public static function addUser($userJoinDto)
    {
        $users_row = $userJoinDto->exportDatabaseForJoin();

        $uid = IntraDb::getGnfDb()->sqlInsert(sqlTable(self::$table), $users_row);
        if (!$uid) {
            throw new MsgException('계정 추가가 실패하였습니다');
        }
        return true;
    }

    public static function isExistById($id)
    {
        return self::getDb()->sqlCount('users', ['id' => $id]);
    }

    public static function getDictWithUid($uid)
    {
        return self::getDb()->sqlDict('select * from ? where uid = ?', sqlTable(self::$table), $uid);
    }

    public static function getDictWithUids(array $uids)
    {
        return self::getDb()->sqlDicts(
            'select * from ? where ?',
            sqlTable(self::$table),
            sqlWhere(['uid' => $uids])
        );
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public static function convertUidFromId($id)
    {
        return self::getDb()->sqlData('select uid from ? where id = ?', sqlTable(self::$table), $id);
    }

    public static function isExistByUid($uid)
    {
        return self::getDb()->sqlData('select count(*) from users where uid = ?', $uid);
    }

    public static function getDictsAvailable()
    {
        $where = [];
        $where['on_date'] = sqlLesserEqual(sqlNow());
        $where['off_date'] = sqlGreaterEqual(sqlNow());

        return self::getDb()->sqlDicts('select * from users where ? order by name', sqlWhere($where));
    }

    public static function getAllDicts()
    {
        return self::getDb()->sqlDicts('select * from users order by off_date desc, on_date');
    }

    public static function getDictsOfManager()
    {
        $where = [];
        $where['on_date'] = sqlLesserEqual(sqlNow());
        $where['off_date'] = sqlGreaterEqual(sqlNow());
        $where['position'] = ['CEO', '팀장', 'CTO', 'CFO', 'CDO', '기타'];

        return self::getDb()->sqlDicts('select * from users where ? order by name', sqlWhere($where));
    }

    public static function updateExtra($uid, $extra_update)
    {
        self::invalidateCache($uid);

        $where = ['uid' => $uid];
        self::getDb()->sqlUpdate(self::$table, $extra_update, $where);

        return 1;
    }

    public static function update($uid, $update)
    {
        self::invalidateCache($uid);

        $where = ['uid' => $uid];
        self::getDb()->sqlUpdate(self::$table, $update, $where);

        return 1;
    }

    public static function getDictsWithTeam($team)
    {
        $where = [];
        $where['on_date'] = sqlLesserEqual(sqlNow());
        $where['off_date'] = sqlGreaterEqual(sqlNow());
        $where['team'] = $team;

        return self::getDb()->sqlDicts('select * from users where ? order by name', sqlWhere($where));
    }
}
