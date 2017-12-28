<?php
namespace Intra\Service\Payment;

use Intra\Core\MsgException;

class FileUploadDtoFactory
{
    public static function createFromDatabaseDicts($payment_files_dicts)
    {
        $return = [];
        if (is_array($payment_files_dicts)) {
            foreach ($payment_files_dicts as $payment_files_dict) {
                $return[] = FileUploadDto::importFromDatabaseDict($payment_files_dict);
            }
        }

        return $return;
    }

    public static function importDtoByPk($id)
    {
        $dict = FileUploadModel::getDictByPk($id);
        if (!$dict) {
            throw new MsgException("파일정보에 오류가 있습니다. (삭제된 파일일 수 있습니다)");
        }

        return FileUploadDto::importFromDatabaseDict($dict);
    }

    public static function createFromGroupId($group, $id)
    {
        $dicts = FileUploadModel::getDictsByGroupAndKeys($group, $id);

        return self::createFromDatabaseDicts($dicts);
    }
}
