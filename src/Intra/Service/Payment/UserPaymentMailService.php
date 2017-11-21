<?php
namespace Intra\Service\Payment;

use Intra\Service\Mail\MailRecipient;
use Intra\Service\User\Organization;
use Intra\Service\User\UserJoinService;
use Mailgun\Mailgun;

class UserPaymentMailService
{
    public static function sendMail($type, $payment_id, $detail, $app)
    {
        $dto = PaymentDtoFactory::createFromDatabaseByPk($payment_id);

        if ($type == '결제반려') {
            $title = "[{$type}][{$dto->team}][{$dto->month}] {$dto->register_name}님의 요청, {$dto->category}";
            $template = 'payments/template/reject.twig';
        } else {
            $title = "[{$type}][{$dto->team}][{$dto->month}] {$dto->register_name}님의 요청, {$dto->category}";
            $template = 'payments/template/add.twig';
        }

        $html = $app['twig']->render($template, ['item' => $dto, 'detail' => $detail]);

        $receivers = self::getReceivers($dto);
        self::sendMailRaw($receivers, $title, $html);
    }

    /**
     * @param $dto
     *
     * @return array
     */
    private static function getReceivers(PaymentDto $dto)
    {
        $receivers = [
            UserJoinService::getEmailByUidSafe($dto->uid),
            UserJoinService::getEmailByUidSafe($dto->manager_uid)
        ];
        if ($dto->category == UserPaymentConst::CATEGORY_USER_BOOK_CANCELMENT) {
            $team = Organization::getTeamName(Organization::ALIAS_CCPQ);
            $receivers_append = UserJoinService::getEmailsByTeam($team);
            $receivers = array_merge($receivers, $receivers_append);
            $receivers = array_unique($receivers);
        }
        if ($dto->category == UserPaymentConst::CATEGORY_USER_DEVICE_CANCELMENT) {
            $team = Organization::getTeamName(Organization::ALIAS_DEVICE);
            $receivers_append = UserJoinService::getEmailsByTeam($team);
            $receivers = array_merge($receivers, $receivers_append);
            $receivers = array_unique($receivers);
        }
        return $receivers;
    }

    /**
     * @param $receivers
     * @param $title
     * @param $html
     */
    private static function sendMailRaw($receivers, $title, $html)
    {
        $receivers = array_merge($receivers, MailRecipient::getMails(MailRecipient::PAYMENT));

        if ($_ENV['INTRA_DEBUG']) {
            $test_mails = $_ENV['INTRA_TEST_MAILS'];
            if (!$test_mails) {
                return true;
            }

            $receivers = explode(',', $test_mails);
        }

        $mg = new Mailgun($_ENV['MAILGUN_API_KEY']);
        $domain = "ridibooks.com";
        $mg->sendMessage(
            $domain,
            [
                'from' => 'noreply@ridibooks.com',
                'to' => implode(', ', $receivers),
                'subject' => $title,
                'html' => $html
            ]
        );
    }
}
