<?php

namespace Intra\Service\Support;

use Intra\Core\MsgException;

class SupportDtoFactory
{
    public static function get($target, $id)
    {
        $dict = SupportModel::getDict($target, $id);
        $columns = SupportPolicy::getColumnFields($target);
        if ($dict === null) {
            throw  new MsgException('해당 자료가 없습니다.');
        }
        $dto = SupportDto::importFromDict($target, $columns, $dict);

        return $dto;
    }
}
