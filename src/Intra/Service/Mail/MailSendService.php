<?php

namespace Intra\Service\Mail;

use Intra\Core\MsgException;
use Mailgun\Mailgun;
use Ridibooks\Platform\Common\DictsUtils;

class MailSendService
{
    /**
     * @param MailingDto[] $dtos
     *
     * @return bool
     * @throws MsgException
     * @throws \Mailgun\Messages\Exceptions\MissingRequiredMIMEParameters
     */
    public static function sends($dtos)
    {
        foreach ($dtos as $dto) {
            if ($_ENV['is_dev']) {
                $test_mails = $_ENV['test_mails'];
                if (!$test_mails) {
                    return true;
                }

                $dto->receiver = explode(',', $test_mails);
            }
            self::send($dto);
        }
        return true;
    }

    /**
     * @param MailingDto $dto
     *
     * @throws MsgException
     * @throws \Mailgun\Messages\Exceptions\MissingRequiredMIMEParameters
     */
    public static function send($dto)
    {
        $mg = new Mailgun($_ENV['mailgun_api_key']);
        $domain = "ridibooks.com";

        $html = $dto->body_header . DictsUtils::convertDictsToHtmlTable($dto->dicts) . $dto->body_footer;
        $mail_post = [
            'from' => 'noreply@ridibooks.com',
            'subject' => $dto->title,
            'html' => $html
        ];

        $dto->receiver = self::filterMails($dto->receiver);
        $dto->replyTo = self::filterMails($dto->replyTo);
        $dto->CC = self::filterMails($dto->CC);
        $dto->BCC = self::filterMails($dto->BCC);

        if (!$dto->receiver) {
            throw new MsgException('empty mail receiver');
        }

        if ($dto->receiver) {
            $mail_post['to'] = $dto->receiver;
        }
        if ($dto->replyTo) {
            $mail_post['h:Reply-To'] = $dto->replyTo;
        }
        if ($dto->CC) {
            $mail_post['cc'] = $dto->CC;
        }
        if ($dto->BCC) {
            $mail_post['bcc'] = $dto->BCC;
        }

        $mg->sendMessage(
            $domain,
            $mail_post
        );
    }

    /**
     * @param $mailReceiver mixed
     *
     * @return \string[]
     * @throws \Exception
     */
    private function filterMails($mailReceiver)
    {
        if (is_null($mailReceiver)) {
            return $mailReceiver;
        }
        if (!is_array($mailReceiver)) {
            throw new MsgException('unexpeced mail list : ' . strval($mailReceiver));
        }
        $mailReceiver = array_unique($mailReceiver);
        $mailReceiver = array_filter($mailReceiver);
        return implode(',', $mailReceiver);
    }
}
