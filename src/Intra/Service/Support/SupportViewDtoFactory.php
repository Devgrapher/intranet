<?php
namespace Intra\Service\Support;

use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;

class SupportViewDtoFactory
{
    public static function gets($columns, $target, $uid, $date, $type)
    {
        $self = UserSession::getSelfDto();
        if ($type == 'remain') {
            if (UserPolicy::isSupportAdmin($self)) {
                $row_dicts = SupportModel::getDictsRemainAll($columns, $target);
            } else {
                $row_dicts = SupportModel::getDictsRemainByAccept($columns, $target, $self->uid);
            }
        } else {
            if (UserPolicy::isSupportAdmin($self)) {
                $row_dicts = SupportModel::getDictsAll($columns, $target, $date);
            } else {
                $row_dicts = SupportModel::getDicts($columns, $target, $uid, $date);
            }
        }

        $support_view_dtos = [];
        foreach ($row_dicts as $row_dict) {
            $support_dto = SupportDto::importFromDict($target, $columns, $row_dict);
            $support_view_dto = SupportViewDto::create($support_dto);
            $support_view_dtos[] = $support_view_dto;
        }
        return $support_view_dtos;
    }

    /**
     * @param           $columns
     * @param           $target
     * @param \DateTime $begin_datetime
     * @param \DateTime $end_datetime
     *
     * @return SupportViewDto[]
     */
    public static function getsForExcel($columns, $target, $begin_datetime, $end_datetime)
    {
        $row_dicts = SupportModel::getDictsForExcel($columns, $target, $begin_datetime, $end_datetime);

        $support_view_dtos = [];
        foreach ($row_dicts as $row_dict) {
            $support_dto = SupportDto::importFromDict($target, $columns, $row_dict);
            $support_view_dto = SupportViewDto::create($support_dto);
            $support_view_dtos[] = $support_view_dto;
        }
        return $support_view_dtos;
    }
}
