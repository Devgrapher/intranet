<?php

namespace Intra\Service\User;

use Intra\Service\Mail\MailingDto;
use Intra\Service\Mail\MailSendService;

class UserMailService
{
    public static function sendMail($type, UserDto $user_dto, $app)
    {
        $mailing_dtos = self::getMailContents($type, $user_dto, $app);
        MailSendService::sends($mailing_dtos);
    }

    private static function getMailContents($type, UserDto $user_dto, $app)
    {
        $title = "[{$type}] {$user_dto->name}님";
        $html = $app['twig']->render('users/template/join_mail.twig', ['item' => $user_dto]);

        $receivers = [];
        $support_all = $_ENV['user_policy.user_manager'];
        if ($support_all) {
            $receivers[] = explode(',', $support_all);
        }
        $receivers = array_unique($receivers);

        $mailing_dto = new MailingDto();
        $mailing_dto->receiver = $receivers;
        $mailing_dto->title = $title;
        $mailing_dto->body_header = $html;

        return [$mailing_dto];
    }
}
