<?php
namespace Intra\Service\Payment;

use Intra\Core\BaseModel;

class FileUploadModel extends BaseModel
{
    /**
     * @param $file_upload_dto FileUploadDto
     */
    public static function insert($file_upload_dto)
    {
        $dict = $file_upload_dto->exportDatabaseInsert();

        return self::getDb()->sqlInsert('files', $dict);
    }

    public static function getAlreadyRegistedCount($group, $key)
    {
        $where = ['group' => $group, 'key' => $key];

        return self::getDb()->sqlCount('files', $where);
    }

    public static function getDictsByGroupAndKeys($group, $keys)
    {
        $where = ['group' => $group, 'key' => $keys, 'del_date' => null];

        return self::getDb()->sqlDicts('select * from files where ?', sqlWhere($where));
    }

    public static function getDictByPk($id)
    {
        $where = ['id' => $id, 'del_date' => null];

        return self::getDb()->sqlDict('select * from files where ?', sqlWhere($where));
    }

    /**
     * @param $id
     * @return int
     */
    public static function remove($id)
    {
        $update = ['is_delete' => 1];
        $where = ['id' => $id];

        return self::getDb()->sqlUpdate('files', $update, $where);
    }
}
