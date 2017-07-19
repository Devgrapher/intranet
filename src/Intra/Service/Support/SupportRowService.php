<?php

namespace Intra\Service\Support;

use Intra\Core\JsonDto;
use Intra\Core\JsonDtoWrapper;
use Intra\Core\MsgException;
use Intra\Service\Support\Column\SupportColumn;
use Intra\Service\Support\Column\SupportColumnAccept;
use Intra\Service\Support\Column\SupportColumnAcceptDatetime;
use Intra\Service\Support\Column\SupportColumnAcceptUser;
use Intra\Service\Support\Column\SupportColumnCategory;
use Intra\Service\Support\Column\SupportColumnComplete;
use Intra\Service\Support\Column\SupportColumnCompleteDatetime;
use Intra\Service\Support\Column\SupportColumnCompleteUser;
use Intra\Service\Support\Column\SupportColumnDate;
use Intra\Service\Support\Column\SupportColumnTeam;
use Intra\Service\Support\Column\SupportColumnText;
use Intra\Service\Support\Column\SupportColumnWorker;
use Intra\Service\User\UserDto;
use Intra\Service\User\UserJoinService;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Symfony\Component\HttpFoundation\JsonResponse;

class SupportRowService
{
    /**
     * @param UserDto    $target_user_dto
     * @param SupportDto $support_dto
     * @param $app
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public static function add($target_user_dto, $support_dto, $app)
    {
        return JsonDtoWrapper::create(function () use ($target_user_dto, $support_dto, $app) {
            $support_dto = SupportDtoFilter::filterAddingDto($target_user_dto, $support_dto);
            $insert_id = SupportModel::add($support_dto);
            if (!$insert_id) {
                throw new MsgException('자료추가 실패했습니다');
            }
            SupportMailService::sendMail($support_dto->target, '추가', $insert_id, $app);

            return new JsonDto('성공했습니다.');
        });
    }

    public static function edit($target, $id, $key, $value, $app)
    {
        $support_dto = SupportDtoFactory::get($target, $id);

        $columns = SupportPolicy::getColumnFields($target);
        $user = UserSession::getSelfDto();
        if (!(self::isEditable($user, $target, $columns, $key, $value, $support_dto))) {
            return $support_dto->dict[$key];
        }
        SupportModel::edit($target, $id, $key, $value);
        $support_dto = SupportDtoFactory::get($target, $id);
        if (SupportPolicy::getColumn($target, $key) instanceof SupportColumnWorker) {
            return UserJoinService::getNameByUidSafe($support_dto->dict[$key]);
        }
        return $support_dto->dict[$key];
    }

    private static function hasAuth($user, $target, $columns, $key, $support_dto)
    {
        if (UserPolicy::isSupportAdmin($user, $target)) {
            return true;
        }

        foreach ($columns as $column) {
            if ($column->key == $key) {
                if ($column instanceof SupportColumnCategory ||
                    $column instanceof SupportColumnText ||
                    $column instanceof SupportColumnDate ||
                    $column instanceof SupportColumnTeam
                ) {
                    $hasAuth = ($support_dto->uid == $user->uid
                        || $column->isEditableForUser($user));
                    return $hasAuth;
                }
                break;
            }
        }

        return false;
    }

    /**
     * @param UserDto         $user
     * @param SupportColumn[] $columns
     * @param                 $key
     * @param SupportDto      $support_dto
     *
     * @return bool
     */
    private static function isEditable($user, $target, $columns, $key, $value, $support_dto)
    {
        if (!self::hasAuth($user, $target, $columns, $key, $support_dto)) {
            return false;
        }

        foreach ($columns as $column) {
            if ($column->key == $key) {
                if ($column instanceof SupportColumnDate) {
                    $d = \DateTime::createFromFormat('Y-m-d', $value);
                    if ($d === false || array_sum($d->getLastErrors())) {
                        return false;
                    }
                }
                break;
            }
        }

        return true;
    }

    public static function del($target, $id, $app)
    {
        return JsonDtoWrapper::create(function () use ($target, $id) {
            $support_dto = SupportDtoFactory::get($target, $id);
            $user = UserSession::getSelfDto();
            self::assertDelete($user, $target, $support_dto);
            $count = SupportModel::del($target, $support_dto->id);
            if (!$count) {
                throw new MsgException('삭제되지 않았습니다.');
            }

            return new JsonDto('삭제되었습니다.');
        });
    }

    private static function assertDelete($user, $target, $support_dto)
    {
        if (UserPolicy::isSupportAdmin($user, $target)) {
            return;
        }
        if ($support_dto->uid == $user->uid) {
            return;
        }
        throw new MsgException('권한이 없습니다.');
    }

    public static function complete($target, $id, $key, $app)
    {
        return JsonDtoWrapper::create(function () use ($target, $id, $key, $app) {
            $self = UserSession::getSelfDto();
            $columns = SupportPolicy::getColumnFields($target);
            $support_dto = SupportDtoFactory::get($target, $id);

            $is_complete = false;

            foreach ($columns as $column) {
                if ($column->key == $key) {
                    if ($column instanceof SupportColumnAcceptUser) {
                        $target_uid = $support_dto->dict[$key];
                        $has_auth = ($target_uid == $self->uid);
                        $is_admin = UserPolicy::isSupportAdmin($self, $target);
                        if (!($has_auth || $is_admin)) {
                            throw new MsgException('권한이 없습니다.');
                        }
                        break;
                    }
                    if ($column instanceof SupportColumnComplete) {
                        $has_auth = ($column->callback_has_user_auth)($self);
                        $is_admin = UserPolicy::isSupportAdmin($self, $target);
                        if (!($has_auth || $is_admin)) {
                            throw new MsgException('권한이 없습니다.');
                        }
                        $is_complete = true;
                        break;
                    }
                }
            }

            foreach ($columns as $column) {
                if ($column instanceof SupportColumnComplete) {
                    if ($column->key == $key) {
                        SupportModel::edit($target, $id, $column->key, 1);
                    }
                } elseif ($column instanceof SupportColumnCompleteUser) {
                    if ($column->parent_column == $key) {
                        SupportModel::edit($target, $id, $column->key, $self->uid);
                    }
                } elseif ($column instanceof SupportColumnCompleteDatetime) {
                    if ($column->parent_column == $key) {
                        SupportModel::edit($target, $id, $column->key, date('Y/m/d H:i:s'));
                    }
                } elseif ($column instanceof SupportColumnAccept) {
                    if ($column->key == $key) {
                        SupportModel::edit($target, $id, $column->key, 1);
                    }
                } elseif ($column instanceof SupportColumnAcceptDatetime) {
                    if ($column->parent_column == $key) {
                        SupportModel::edit($target, $id, $column->key, date('Y/m/d H:i:s'));
                    }
                }
            }

            if ($is_complete) {
                $result = '완료';
            } else {
                $result = '승인';
            }
            SupportMailService::sendMail($target, $result, $id, $app);

            return new JsonDto('승인되었습니다.');
        });
    }
}
