<?php

namespace Intra\Service\Support;

use Intra\Core\MsgException;
use Intra\Service\Payment\FileUploadDtoFactory;
use Intra\Service\Payment\FileUploadService;
use Intra\Service\Support\Column\SupportColumn;
use Intra\Service\Support\Column\SupportColumnAccept;
use Intra\Service\Support\Column\SupportColumnAcceptUser;
use Intra\Service\Support\Column\SupportColumnComplete;
use Intra\Service\User\UserDto;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SupportFileService
{
    /**
     * @param $target
     * @param $id
     * @param $column_key
     * @param $file UploadedFile
     *
     * @return false|UploadedFile
     */
    public static function addFiles($target, $id, $column_key, $file)
    {
        $self = UserSession::getSelfDto();
        $columns = SupportPolicy::getColumnFields($target);
        $support_dto = SupportDtoFactory::get($target, $id);
        self::assertAccessFiles($support_dto, $self, $target, $columns);

        $file_upload_service = new FileUploadService('support.' . $target . '.' . $column_key);
        return $file_upload_service->upload($self->uid, $id, $file);
    }

    /**
     * @param SupportDto      $support_dto
     * @param                 $self
     * @param SupportColumn[] $columns
     *
     * @throws MsgException
     */
    private static function assertAccessFiles($support_dto, $self, $target, $columns)
    {
        if (UserPolicy::isSupportAdmin($self, $target)) {
            return;
        }
        $has_auth = false;
        foreach ($columns as $column) {
            if ($column instanceof SupportColumnAcceptUser) {
                $accept_usr_uid = $support_dto->dict[$column->key];
                if ($accept_usr_uid == $self->uid) {
                    $has_auth = true;
                }
            } elseif ($column instanceof SupportColumnComplete) {
                if (($column->callback_has_user_auth)($self)) {
                    $has_auth = true;
                }
            }
        }
        if ($self->uid != $support_dto->uid && !$has_auth) {
            throw new MsgException("본인이나 승인자만 파일을 업로드 가능합니다.");
        }
    }

    /**
     * @param UserDto $self
     * @param         $target
     * @param         $fileid
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
     */
    public static function downloadFile($self, $target, $fileid)
    {
        $file_upload_dto = FileUploadDtoFactory::importDtoByPk($fileid);
        $support_dto = SupportDtoFactory::get($target, $file_upload_dto->key);
        $columns = SupportPolicy::getColumnFields($target);
        self::assertAccessFiles($support_dto, $self, $target, $columns);

        $file_upload_service = new FileUploadService($file_upload_dto->group);
        return $file_upload_service->getBinaryFileResponseWithDto($file_upload_dto);
    }

    public static function deleteFile($self, $target, $fileid)
    {
        $file_upload_dto = FileUploadDtoFactory::importDtoByPk($fileid);
        $support_dto = SupportDtoFactory::get($target, $file_upload_dto->key);
        $columns = SupportPolicy::getColumnFields($target);
        self::assertAccessFiles($support_dto, $self, $target, $columns);
        self::assertDeleteFile($support_dto, $self, $target, $columns);

        $file_upload_service = new FileUploadService('payment_files');
        return $file_upload_service->remove($file_upload_dto);
    }

    private static function assertDeleteFile($support_dto, $self, $target, $columns)
    {
        if (UserPolicy::isSupportAdmin($self, $target)) {
            return;
        }
        $is_not_done = false;
        foreach ($columns as $column) {
            if ($column instanceof SupportColumnAccept ||
                $column instanceof SupportColumnComplete
            ) {
                $is_accepted = $support_dto->columns[$column->key];
                if (!$is_accepted) {
                    $is_not_done = true;
                }
            }
        }
        if (!$is_not_done) {
            throw new MsgException("승인된 이후에는 재무팀만 변경할 수 있습니다. 파일을 재무팀에 전달해주세요.");
        }
    }
}
