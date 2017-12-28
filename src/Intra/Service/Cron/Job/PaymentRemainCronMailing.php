<?php

namespace Intra\Service\Cron\Job;

use Intra\Model\PaymentModel;
use Intra\Service\Cron\Interfacer\CronMailingInterface;
use Intra\Service\Mail\MailingDto;
use Intra\Service\Mail\MailRecipient;
use Intra\Service\Payment\PaymentDtoFactory;
use Intra\Service\User\UserJoinService;
use Ridibooks\Platform\Common\ObjectsUtils;

class PaymentRemainCronMailing extends CronMailingInterface
{
    /**
     * @return string
     */
    public function getUniqueName()
    {
        return 'PaymentRemainCronMailing';
    }

    /**
     * @param $last_executed_datetime \DateTime
     * @return bool
     */
    public function isTimeToRun($last_executed_datetime)
    {
        if ($this->isToday($last_executed_datetime)) {
            return false;
        }
        $hour = date('H');
        if ($hour < 10) {
            return false;
        }

        return true;
    }

    /**
     * @return \Intra\Service\Mail\MailingDto[]
     */
    public function getMailContentsDtos()
    {
        $dto_template = new MailingDto();
        $dto_template->replyTo = MailRecipient::getMails(MailRecipient::PAYMENT);
        $dto_template->title = '[승인요청] ' . date('Y-m-d') . ' 결제 미승인 내역';
        $dto_template->body_header = date('Y-m-d') . " 현재, 아래 결제 요청이 승인되지 않았습니다.<br/>
결제를 위하여 인트라넷에 접속해 아래 결제내역을 승인처리 해주세요.<br/>
<br/>
<a href='http://intra.ridi.com/payments/remain'>승인하러가기</a><br/><br/><br/>";

        $return_dtos = [];
        $dicts = PaymentModel::getManagerNotYetAccepts();
        $payments = PaymentDtoFactory::importFromDatabaseDicts($dicts);
        $payments_by_uid = ObjectsUtils::alignListByKey($payments, 'manager_uid');

        foreach ($payments_by_uid as $uid => $payments) {
            $first_payment = $payments[0];
            $dto = clone $dto_template;
            $dto->receiver = [
                UserJoinService::getEmailByUidSafe($first_payment->manager_uid),
            ];
            $dto->CC = MailRecipient::getMails(MailRecipient::PAYMENT);
            $dto->dicts = [];
            foreach ($payments as $payment) {
                $dto->dicts[] =
                    [
                        '요청일' => $this->reformatDatetime('Y/m/d', $payment->request_date),
                        '요청자' => $payment->register_name,
                        '결제 예정일' => $this->reformatDatetime('Y/m/d', $payment->pay_date),
                        '귀속부서' => $payment->team,
                        '프로덕트' => $payment->product,
                        '분류' => $payment->category,
                        '상세내역' => $payment->desc,
                        '비고' => $payment->note,
                        '금액' => number_format($payment->price),
                        '결제수단' => $payment->paytype,
                        '상태' => $payment->status,
                    ];
            }
            $return_dtos[] = $dto;
        }

        return $return_dtos;
    }
}
