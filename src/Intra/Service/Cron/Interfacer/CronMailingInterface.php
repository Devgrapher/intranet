<?php

namespace Intra\Service\Cron\Interfacer;

use Intra\Core\MsgException;
use Intra\Service\Mail\MailingDto;
use Intra\Service\Mail\MailSendService;

abstract class CronMailingInterface extends CronInterface
{
    /**
     * @return MailingDto[]
     */
    abstract public function getMailContentsDtos();

    /**
     * @param $last_executed_datetime \DateTime
     * @return bool
     */
    public function isToday($last_executed_datetime)
    {
        $last_date = $last_executed_datetime->format('Y/m/d');
        $today_date = date('Y/m/d');
        if ($last_date == $today_date) {
            return true;
        }

        return false;
    }

    public function reformatDatetime($format, $request_date)
    {
        $datetime = new \DateTime($request_date);

        return $datetime->format($format);
    }

    /**
     * @return bool
     * @throws MsgException
     */
    public function run()
    {
        $dtos = $this->getMailContentsDtos();
        if (!is_array($dtos)) {
            throw new MsgException('invalid getMailContentsDtos : ' . get_called_class());
        }
        MailSendService::sends($dtos);

        return true;
    }
}
