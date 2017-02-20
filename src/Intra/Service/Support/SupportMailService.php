<?php

namespace Intra\Service\Support;

use Intra\Service\Mail\MailingDto;
use Intra\Service\Mail\MailSendService;
use Intra\Service\Support\Column\SupportColumnAcceptUser;
use Intra\Service\Support\Column\SupportColumnCompleteUser;
use Intra\Service\Support\Column\SupportColumnDate;
use Intra\Service\User\UserJoinService;

class SupportMailService
{
    public static function sendMail($target, $type, $id, $app)
    {
        $support_dto = SupportDtoFactory::get($target, $id);
        $mailing_dtos = self::getMailContents($target, $type, $support_dto, $app);
        MailSendService::sends($mailing_dtos);
    }

    /**
     * @param            $target
     * @param            $type
     * @param SupportDto $support_dto
     * @param $app
     *
     * @return MailingDto[]
     */
    private static function getMailContents($target, $type, $support_dto, $app)
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
        $register_name = UserJoinService::getNameByUidSafe($support_dto->uid);

        $title = "[{$title}][{$type}][{$working_date}] {$register_name}님의 요청";
        $link = 'http://intra.' . $_ENV['domain'] . '/support/' . $target . '/remain';
        $html = $app['twig']->render(
            'support/template/mail.twig',
            [
                'dto' => $support_view_dto,
                'columns' => $column_fields,
                'link' => $link,
            ]
        );

        $receivers = [UserJoinService::getEmailByUidSafe($support_dto->uid)];
        foreach ($uids as $uid) {
            $receivers[] = UserJoinService::getEmailByUidSafe($uid);
        }
        $support_all = $_ENV['user_policy.support_admin.all'];
        if ($support_all) {
            $receivers = array_merge($receivers, explode(',', $support_all));
        }
        $support_target = $_ENV["user_policy.support_admin.$target"];
        if ($support_target) {
            $receivers = array_merge($receivers, explode(',', $support_target));
        }
        $receivers = array_unique($receivers);

        $mailing_dto = new MailingDto();
        $mailing_dto->receiver = $receivers;
        $mailing_dto->title = $title;
        $mailing_dto->body_header = $html;

        return [$mailing_dto];
    }
}
