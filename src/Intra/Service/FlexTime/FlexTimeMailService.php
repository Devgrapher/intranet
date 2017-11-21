<?php

namespace Intra\Service\FlexTime;

use Intra\Model\FlexTimeModel;
use Intra\Service\Mail\MailingDto;
use Intra\Service\Mail\MailSendService;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserJoinService;

class FlexTimeMailService
{
    private static function getMailReceivers(FlexTimeModel $flextime)
    {
        $uids = [$flextime->uid, $flextime->manager_uid, $flextime->keeper_uid];
        $uids = array_filter(array_unique($uids));

        $users = UserDtoFactory::createDtosByUid($uids);

        $emails = [];
        foreach ($users as $user) {
            $emails[] = $user->id . '@' . $_ENV['domain'];
        }
        if ($_ENV['recipients_holiday']) {
            $emails = array_merge($emails, explode(',', $_ENV['recipients_holiday']));
        }

        return array_unique(array_filter($emails));
    }

    public static function sendMail(FlexTimeModel $flextime, $type, $app)
    {
        $today = date('Y-m-d');
        $title = "[얼리파마][{$type}][{$today}] {$flextime->name}님의 요청";
        $receivers = self::getMailReceivers($flextime);

        $flextime->uid_name = UserJoinService::getNameByUidSafe($flextime->uid);
        $flextime->manager_uid_name = UserJoinService::getNameByUidSafe($flextime->manager_uid);
        $flextime->keeper_uid_name = UserJoinService::getNameByUidSafe($flextime->keeper_uid);
        $html = $app['twig']->render('flextime/template/mail.twig', [
            'flextime' => $flextime,
        ]);

        $mailing_dto = new MailingDto();
        $mailing_dto->receiver = $receivers;
        $mailing_dto->title = $title;
        $mailing_dto->body_header = $html;

        return MailSendService::sends([$mailing_dto]);
    }
}
