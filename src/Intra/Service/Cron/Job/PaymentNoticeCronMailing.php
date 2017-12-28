<?php

namespace Intra\Service\Cron\Job;

use Intra\Model\PaymentModel;
use Intra\Service\Cron\Interfacer\CronMailingInterface;
use Intra\Service\Mail\MailingDto;
use Intra\Service\Mail\MailRecipient;
use Intra\Service\Payment\PaymentDtoFactory;
use Intra\Service\User\UserJoinService;
use Ridibooks\Platform\Common\ObjectsUtils;

class PaymentNoticeCronMailing extends CronMailingInterface
{
    /**
     * @return string
     */
    public function getUniqueName()
    {
        return 'PaymentNoticeCronMailing';
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
     * @return MailingDto[]
     */
    public function getMailContentsDtos()
    {
        $dto_template = new MailingDto();
        $dto_template->replyTo = MailRecipient::getMails(MailRecipient::PAYMENT);
        $dto_template->title = '[확인요청] ' . date('Y-m-d') . ' 결제 예정 내역';
        $dto_template->body_header = date('Y-m-d') . "에 아래 결제가 완료될 예정입니다.<br/>
        혹시 변동사항이 있는지 확인해 주세요.<br/><br/><br/>";

        $return_dtos = [];
        $dicts = PaymentModel::getPaydayIsAfter3days();
        $payments = PaymentDtoFactory::importFromDatabaseDicts($dicts);
        $payments_by_uid = ObjectsUtils::alignListByKey($payments, 'uid');

        foreach ($payments_by_uid as $uid => $payments) {
            $first_payment = $payments[0];
            $dto = clone $dto_template;
            $dto->receiver = [
                UserJoinService::getEmailByUidSafe($first_payment->uid),
            ];
            $dto->CC = [
                UserJoinService::getEmailByUidSafe($first_payment->manager_uid)
            ];
            $dto->CC = array_merge($dto->CC, MailRecipient::getMails(MailRecipient::PAYMENT));
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
