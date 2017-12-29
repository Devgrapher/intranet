<?php
namespace Intra\Service\Payment;

use Intra\Core\BaseDto;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadDto extends BaseDto
{
    public $uid;
    public $group;
    public $key;
    public $original_filename;
    public $location;

    // view only
    public $id;
    public $reg_date;

    /**
     * @param $uid
     * @param $file UploadedFile
     * @param $group
     * @param $key
     * @param $count
     * @return FileUploadDto
     */
    public static function importFromUploadReqeust($uid, $file, $group, $key, $count)
    {
        $return = new self();
        $return->uid = $uid;
        $return->group = $group;
        $return->key = $key;
        $return->original_filename = $file->getClientOriginalName();
        $return->location = $group . '/' . $key . "." . $count . "." . $file->getClientOriginalExtension();

        return $return;
    }

    public static function importFromDatabaseDict($payment_files_dict)
    {
        $return = new self();
        $return->initFromArray($payment_files_dict);

        return $return;
    }

    public function exportDatabaseInsert()
    {
        return $this->exportAsArrayExceptNull();
    }
}
