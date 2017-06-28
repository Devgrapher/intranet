<?php

namespace Intra\Service\Support;

use Intra\Core\MsgException;
use Intra\Service\Support\Column\SupportColumnCategory;
use Intra\Service\Support\Column\SupportColumnMutual;
use Intra\Service\User\UserDto;

class SupportDtoFilter
{
    /**
     * @param UserDto $target_user_dto
     * @param SupportDto $support_dto
     *
     * @return SupportDto
     * @throws MsgException
     */
    public static function filterAddingDto($target_user_dto, $support_dto)
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

        if ($support_dto->target == SupportPolicy::TYPE_FAMILY_EVENT) {
            $category = $support_dto->dict[$columns['분류']->key];

            if ($category == '결혼') {
                $flower_type_column = '화환';
            } elseif (in_array($category, ['자녀출생', '졸업', '장기근속(3년)'])) {
                $flower_type_column = '과일바구니';
            } elseif (in_array($category, ['사망-형제자매 (배우자 형제자매포함)', '사망-부모 (배우자 부모 포함)', '사망-조부모 (배우자 조부모 포함)'])) {
                $flower_type_column = '조화';
            } else {
                $flower_type_column = '기타';
            }

            if ($support_dto->dict[$columns['대상자']->key] == '외부') {
                if ($support_dto->dict[$columns['화환 종류']->key] != '기타') {
                    throw new MsgException('대상자가 외부일 경우, 화환 종류를 기타로 선택 후 직접 입력해주세요.');
                }
            } else {
                if ($support_dto->dict[$columns['화환 종류']->key] == '자동선택') {
                    $support_dto->dict[$columns['화환 종류']->key] = $flower_type_column;
                }
            }

            if (in_array(
                $category,
                [
                    '결혼',
                    '자녀출생',
                    '사망-부모 (배우자 부모 포함)',
                ]
            )) {
                $cash = '1000000';
                $support_dto->dict[$columns['경조금']->key] = $cash;
            }

            $flower_datetime = trim($support_dto->dict[$columns['화환 도착일시']->key]);
            $flower_datetime_parsed = date_create($flower_datetime . ':00');
            if ($flower_datetime_parsed === false) {
                throw new MsgException('화환 도착일시를 다시 확인해주세요');
            }
        } elseif ($support_dto->target == SupportPolicy::TYPE_BUSINESS_CARD) {
            if ($support_dto->dict[$columns['제작(예정)일']->key] == '') {
                $support_dto->dict[$columns['제작(예정)일']->key] = date("Y-m-t");
            }
        } elseif ($support_dto->target == SupportPolicy::TYPE_DEPOT) {
            $request_date = $support_dto->dict[$columns['구매예정일']->key];
            $request_datetime = date_create($request_date);
            if ($request_datetime === false) {
                throw new MsgException('날짜입력을 다시 확인해주세요');
            }
        } elseif ($support_dto->target == SupportPolicy::TYPE_GIFT_CARD_PURCHASE) {
            if ($support_dto->dict[$columns['신청매수']->key] <= 0 ||
                $support_dto->dict[$columns['신청금액']->key] <= 0) {
                throw new MsgException('신청 매수와 금액을 확인해주세요');
            }
            if (empty($support_dto->dict[$columns['입금자명']->key])) {
                throw new MsgException('입금자명을 입력해주세요');
            }
            if ($support_dto->dict[$columns['신청매수']->key] < $support_dto->dict[$columns['봉투수량']->key]) {
                throw new MsgException('봉투수량은 최대 신청매수까지 입력할 수 있습니다.');
            }
            $input_due = $support_dto->dict[$columns['입금예정일시(24시간 내)']->key];
            $max_due = date('Y/m/d H:i', strtotime('+1 day'));
            if ($input_due > $max_due) {
                throw new MsgException('입금예정일시는 24시간내로 설정하여 주세요');
            }
        }

        $support_dto->dict['uuid'] = self::getUuidHeader($support_dto->target);

        return $support_dto;
    }

    private static function getUuidHeader($target)
    {
        $year = substr(date('Y'), 2, 2);
        $codes = [
            SupportPolicy::TYPE_BUSINESS_CARD => 'bs',
            SupportPolicy::TYPE_DEPOT => 'pe',
            SupportPolicy::TYPE_FAMILY_EVENT => 'bt',
            SupportPolicy::TYPE_GIFT_CARD_PURCHASE => 'gp',
            SupportPolicy::TYPE_DEVICE => 'hp',
        ];
        $code = $codes[$target];

        return "ridi-{$code}-{$year}";
    }
}
