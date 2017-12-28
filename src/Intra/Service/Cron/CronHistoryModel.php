<?php
namespace Intra\Service\Cron;

use Intra\Core\BaseModel;

class CronHistoryModel extends BaseModel
{
    public static function getLastTime($cron_class_name)
    {
        return self::getDb()->sqlData('select max(reg_date) from cron_history where signature=?', $cron_class_name);
    }

    public static function logExecuted($cron_class_name)
    {
        $insert = ['signature' => $cron_class_name];

        return self::getDb()->sqlInsert('cron_history', $insert);
    }
}
