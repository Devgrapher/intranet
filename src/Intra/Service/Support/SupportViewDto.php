<?php

namespace Intra\Service\Support;

use Intra\Service\Payment\FileUploadDto;
use Intra\Service\Payment\FileUploadDtoFactory;
use Intra\Service\Support\Column\SupportColumnAccept;
use Intra\Service\Support\Column\SupportColumnAcceptDatetime;
use Intra\Service\Support\Column\SupportColumnAcceptUser;
use Intra\Service\Support\Column\SupportColumnComplete;
use Intra\Service\Support\Column\SupportColumnCompleteDatetime;
use Intra\Service\Support\Column\SupportColumnCompleteUser;
use Intra\Service\Support\Column\SupportColumnFile;
use Intra\Service\Support\Column\SupportColumnViewOnly;
use Intra\Service\Support\Column\SupportColumnRegisterUser;
use Intra\Service\Support\Column\SupportColumnWorker;
use Intra\Service\User\UserJoinService;
use Intra\Service\User\UserSession;

class SupportViewDto
{
    /**
     * @var SupportDto
     */
    public $support_dto;
    /**
     * @var FileUploadDto[][]
     */
    public $files = [];
    /**
     * @var
     */
    public $display_dict;
    /**
     * @var
     */
    public $completes_dict;
    /**
     * @var
     */
    public $is_all_complted;

    /**
     * @param SupportDto $support_dto
     *
     * @return SupportViewDto
     */
    public static function create($support_dto)
    {
        $support_viewing_dto = new self($support_dto);
        return $support_viewing_dto;
    }

    /**
     * @param SupportDto $support_dto
     */
    public function __construct($support_dto)
    {
        $this->filterViewDict($support_dto);
    }

    /**
     * @param SupportDto $support_dto
     */
    public function filterViewDict($support_dto)
    {
        $target = $support_dto->target;
        $columns = SupportPolicy::getColumnFields($target);
        $display_dict = $support_dto->dict;
        $self = UserSession::getSelfDto();

        $this->is_all_complted = true;
        $this->completes_dict = [];
        $map_key_to_acceptuid = [];
        foreach ($columns as $column) {
            if ($column instanceof SupportColumnComplete) {
                $key = $column->key;
                $this->completes_dict[$key] = $display_dict[$key];
                if (!$display_dict[$key]) {
                    $this->is_all_complted = false;
                }
            } elseif ($column instanceof SupportColumnAccept) {
                $key = $column->key;
                $this->completes_dict[$key] = $display_dict[$key];
                if (!$display_dict[$key]) {
                    $this->is_all_complted = false;
                }
            } elseif ($column instanceof SupportColumnAcceptUser) {
                $key = $column->key;
                $parent_key = $column->parent_column;
                $map_key_to_acceptuid[$parent_key] = $display_dict[$key];
            }
        }

        foreach ($columns as $column) {
            if ($column instanceof SupportColumnComplete) {
                $key = $column->key;
                if ($this->completes_dict[$key]) {
                    $display_dict[$key] = '승인됨';
                } else {
                    $has_user_auth = ($column->callback_has_user_auth)($self);
                    if ($has_user_auth) {
                        $display_dict[$key] = '승인가능';
                    } else {
                        $display_dict[$key] = '승인안됨';
                    }
                }
            } elseif ($column instanceof SupportColumnAccept) {
                $key = $column->key;
                if ($this->completes_dict[$key]) {
                    $display_dict[$key] = '승인됨';
                } else {
                    if ($map_key_to_acceptuid[$key] == $self->uid) {
                        $display_dict[$key] = '승인가능';
                    } else {
                        $display_dict[$key] = '승인안됨';
                    }
                }
            } elseif ($column instanceof SupportColumnRegisterUser ||
                $column instanceof SupportColumnWorker
            ) {
                $key = $column->key;
                $uid = $display_dict[$key];
                $display_dict[$key] = UserJoinService::getNameByUidSafe($uid);
            } elseif ($column instanceof SupportColumnCompleteUser) {
                $parent_key = $column->parent_column;
                $key = $column->key;
                if ($this->completes_dict[$parent_key]) {
                    $uid = $display_dict[$key];
                    $display_dict[$key] = UserJoinService::getNameByUidSafe($uid);
                } else {
                    unset($display_dict[$key]);
                }
            } elseif ($column instanceof SupportColumnAcceptUser) {
                $key = $column->key;
                $uid = $display_dict[$key];
                $display_dict[$key] = UserJoinService::getNameByUidSafe($uid);
            } elseif ($column instanceof SupportColumnCompleteDatetime ||
                $column instanceof SupportColumnAcceptDatetime
            ) {
                $parent_key = $column->parent_column;
                $key = $column->key;
                if (!$this->completes_dict[$parent_key]) {
                    unset($display_dict[$key]);
                }
            } elseif ($column instanceof SupportColumnFile) {
                $key = $column->key;
                $files_dtos = FileUploadDtoFactory::createFromGroupId('support.' . $target . '.' . $key, $display_dict['id']);
                $this->files[$key] = $files_dtos;
            } elseif ($column instanceof SupportColumnViewOnly) {
                $key = $column->key;
                $display_dict[$key] = ($column->dataCallback)($support_dto);
            }
        }

        $this->display_dict = $display_dict;
    }
}
