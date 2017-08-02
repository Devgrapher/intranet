<?php

namespace Intra\Service\Support;

use Intra\Core\MsgException;
use Intra\Service\Support\Column\SupportColumn;
use Intra\Service\Support\Column\SupportColumnAcceptUser;
use Intra\Service\Support\Column\SupportColumnCategory;
use Intra\Service\Support\Column\SupportColumnComplete;
use Intra\Service\Support\Column\SupportColumnDate;
use Intra\Service\Support\Column\SupportColumnDatetime;
use Intra\Service\Support\Column\SupportColumnMoney;
use Intra\Service\Support\Column\SupportColumnMutual;
use Intra\Service\Support\Column\SupportColumnRegisterEmail;
use Intra\Service\Support\Column\SupportColumnRegisterUser;
use Intra\Service\Support\Column\SupportColumnTeam;
use Intra\Service\Support\Column\SupportColumnText;
use Intra\Service\Support\Column\SupportColumnTextDetail;
use Intra\Service\Support\Column\SupportColumnWorker;
use Intra\Service\User\UserJoinService;
use Symfony\Component\HttpFoundation\Request;

class SupportDto
{
    public $target;
    public $dict;
    public $is_all_completed;

    /**
     * view only
     */
    public $id;
    public $uid;
    public $columns_view;

    /**
     * @param Request         $request
     * @param int             $uid
     * @param SupportColumn[] $columns
     *
     * @return SupportDto
     * @throws MsgException
     */
    public static function importFromAddRequest($request, $uid, $columns)
    {
        $dto = new self();
        $dto->target = $request->get('target');
        $dto->dict = [];
        $dto->is_all_completed = false;

        foreach ($columns as $column_name => $column) {
            if ($column instanceof SupportColumnCategory
                || $column instanceof SupportColumnTeam
                || $column instanceof SupportColumnWorker
                || $column instanceof SupportColumnText
                || $column instanceof SupportColumnDate
                || $column instanceof SupportColumnAcceptUser
                || $column instanceof SupportColumnMutual
                || $column instanceof SupportColumnTextDetail
                || $column instanceof SupportColumnMoney
                || $column instanceof SupportColumnDatetime
            ) {
                $key = $column->key;
                $value = $request->get($key);
                if ($value === null) {
                    $value = '';
                }
                $dto->dict[$key] = $value;
            } elseif ($column instanceof SupportColumnRegisterUser) {
                $key = $column->key;
                $value = $uid;
                $dto->dict[$key] = $value;
            } elseif ($column instanceof SupportColumnRegisterEmail) {
                $key = $column->key;
                $value = UserJoinService::getEmailByUidSafe($uid);
                $dto->dict[$key] = $value;
            }
        }
        return $dto;
    }

    /**
     * @param string          $target
     * @param SupportColumn[] $columns
     * @param array           $dict
     *
     * @return SupportDto
     */
    public static function importFromDict($target, $columns, $dict)
    {
        $dto = new self();
        $dto->id = $dict['id'];
        $dto->target = $target;
        $dto->dict = $dict;
        $dto->is_all_completed = true;

        foreach ($columns as $column_name => $column) {
            if ($column instanceof SupportColumnRegisterUser) {
                $dto->uid = $dict[$column->key];
            } elseif ($column instanceof SupportColumnComplete) {
                if (!$dict[$column->key]) {
                    $dto->is_all_completed = false;
                }
            }
        }
        return $dto;
    }

    public function exportDictAddRequest()
    {
        $dict = [];
        foreach ($this->dict as $key => $column) {
            $dict[$key] = $column;
        }
        return $dict;
    }
}
