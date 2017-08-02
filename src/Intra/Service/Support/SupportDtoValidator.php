<?php

namespace Intra\Service\Support;

use Intra\Core\MsgException;
use Intra\Service\Support\Column\SupportColumnCategory;
use Intra\Service\Support\Column\SupportColumnMutual;
use Intra\Service\User\UserDto;

class SupportDtoValidator
{
    public static function validateAcceptingDto($support_dto)
    {
        SupportPolicy::validateFieldsOnAccept($support_dto);
    }

    /**
     * @param UserDto $target_user_dto
     * @param SupportDto $support_dto
     *
     * @return SupportDto
     * @throws MsgException
     */
    public static function validateAddingDto($target_user_dto, $support_dto)
    {
        $columns = SupportPolicy::getColumnFields($support_dto->target);

        $disabled_column_names = [];

        foreach ($columns as $column_name => $column) {
            if ($column instanceof SupportColumnMutual) {
                $column_value = $support_dto->dict[$column->key];
                if (!in_array($column_value, array_keys($column->groups))) {
                    throw new MsgException($column_name . '의 입력에 선택불가능한값이 선택되었습니다. 정상으로 보이는데 문제가 반복되면 다른 항목을 선택 후 원하는 항목을 다시 선택해주세요.');
                }
                foreach ($column->groups as $group_name => $group_items) {
                    if ($column_value == $group_name) {
                        continue;
                    }
                    $disabled_column_names = array_merge($disabled_column_names, $group_items);
                }
            }
        }

        foreach ($columns as $column_name => $column) {
            if (in_array($column_name, $disabled_column_names)) {
                continue;
            }

            if (!$column->isVisible($target_user_dto)) {
                continue;
            }

            if ($column->required) {
                $column_value = $support_dto->dict[$column->key];
                $column_value = trim($column_value);
                if (strlen($column_value) == 0) {
                    throw new MsgException('"' . $column_name . '"의 입력이 없습니다. 입력해주세요!');
                }
            }
            if ($column instanceof SupportColumnCategory) {
                $column_value = $support_dto->dict[$column->key];
                if (!in_array($column_value, $column->category_items)) {
                    throw new MsgException($column_name . '의 입력에 선택불가능한값이 입력되었습니다. 정상으로 보이는데 문제가 반복되면 다른 항목을 선택 후 원하는 항목을 다시 선택해주세요.');
                }
            }
        }

        SupportPolicy::validateFieldsOnAdd($support_dto);

        $support_dto->dict['uuid'] = self::getUuidHeader($support_dto->target);

        return $support_dto;
    }

    private static function getUuidHeader($target)
    {
        $year = substr(date('Y'), 2, 2);
        $code = SupportPolicy::CODES[$target];

        return "ridi-{$code}-{$year}";
    }
}
