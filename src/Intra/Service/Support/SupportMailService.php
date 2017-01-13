<?php

namespace Intra\Service\Support;

use Intra\Config\Config;
use Intra\Core\Application;
use Intra\Service\Mail\MailingDto;
use Intra\Service\Mail\MailSendService;
use Intra\Service\Support\Column\SupportColumnAcceptUser;
use Intra\Service\Support\Column\SupportColumnCompleteUser;
use Intra\Service\Support\Column\SupportColumnDate;
use Intra\Service\User\UserJoinService;

class SupportMailService
{
    public static function sendMail($target, $type, $id)
    {
        $support_dto = SupportDtoFactory::get($target, $id);
        $mailing_dtos = self::getMailContents($target, $type, $support_dto);
        MailSendService::sends($mailing_dtos);
    }

    /**
     * @param            $target
     * @param            $type
     * @param SupportDto $support_dto
     *
     * @return MailingDto[]
     */
    private static function getMailContents($target, $type, $support_dto)
    {
        $support_view_dto = SupportViewDto::create($support_dto);
        $title = SupportPolicy::getColumnTitle($target);
        $column_fields = SupportPolicy::getColumnFields($target);
        $uids = [];
        $working_date = '';
        foreach ($column_fields as $column_field) {
            if ($column_field instanceof SupportColumnAcceptUser ||
                $column_field instanceof SupportColumnCompleteUser
            ) {
                $uids[] = $support_dto->dict[$column_field->key];
            } elseif ($column_field instanceof SupportColumnDate && $column_field->is_ordering_column) {
                $working_date = $support_dto->dict[$column_field->key];
            }
        }
        $uids = array_unique(array_filter($uids));
        $register_name = UserJoinService::getEmailByUidSafe($support_dto->uid);

        $title = "[{$title}][{$type}][{$working_date}] {$register_name}님의 요청";
        $link = 'http://intra.' . Config::$domain . '/Support/' . $target;
        $html = Application::$view->render(
            'support/template/mail',
            [
                'dto' => $support_view_dto,
                'columns' => $column_fields,
                'link' => $link,
            ]
        );

        $receivers = [
            $register_name,
        ];
        foreach ($uids as $uid) {
            $receivers[] = UserJoinService::getEmailByUidSafe($uid);
        }
        foreach (Config::$user_policy['support_admin']['all'] as $mail) {
            $receivers[] = $mail;
        }
        foreach (Config::$user_policy['support_admin'][$target] as $mail) {
            $receivers[] = $mail;
        }
        $receivers = array_unique($receivers);

        $mailing_dto = new MailingDto();
        $mailing_dto->receiver = $receivers;
        $mailing_dto->title = $title;
        $mailing_dto->body_header = $html;

        return [$mailing_dto];
    }
}
