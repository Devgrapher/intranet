<?php
namespace Intra\Service\Holiday;

use Intra\Service\User\UserDto;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserDtoHandler;
use Mailgun\Mailgun;

class UserHolidayNotification
{
    /**
     * @var UserDto
     */
    private $user;
    /**
     * @var UserHolidayDto[]
     */
    private $holiday_raws;
    private $action_type;

    /**
     * @param $user
     * @param $holiday_raws []
     * @param $action_type
     */
    public function __construct($user, $holiday_raws, $action_type)
    {
        $this->user = $user;
        $this->holiday_raws = $holiday_raws;
        $this->action_type = $action_type;
    }

    public function sendNotification()
    {
        $title = $this->getMailTitle();
        $ret = $this->sendMailNotification($title);
        //$this->sendSlackNotification($title);

        return $ret;
    }

    /**
     * @return string
     */
    private function getMailTitle()
    {
        $cost_sum = $this->getHolidaySumCost();
        $date_duration = $this->getHolidayDuration();
        $holiday_type = $this->getHolidayType();

        return "[{$this->action_type}][{$holiday_type}][{$date_duration}] {$this->user->name}님의 {$cost_sum}일 휴가사용신청";
    }

    /**
     * @param $title
     *
     * @return bool
     * @throws \Exception
     */
    private function sendMailNotification($title)
    {
        $receivers = $this->getMailReceivers();
        $contents = $this->getMailContents();

        if ($_ENV['is_dev']) {
            $test_mails = $_ENV['test_mails'];
            if (!$test_mails) {
                return true;
            }

            $receivers = explode(',', $test_mails);
        }

        $mg = new Mailgun($_ENV['mailgun_api_key']);
        $domain = "ridibooks.com";
        $ret = $mg->sendMessage(
            $domain,
            [
                'from' => 'noreply@ridibooks.com',
                'to' => implode(', ', $receivers),
                'subject' => $title,
                'text' => $contents
            ]
        );
        return $ret->http_response_code == 200;
    }

    /**
     * @return array
     */
    private function getMailReceivers()
    {
        $holiday_raw = $this->holiday_raws[0];
        $uids = [$holiday_raw->uid, $holiday_raw->manager_uid, $holiday_raw->keeper_uid];
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

    /**
     * @return string
     * @throws \Exception
     */
    private function getMailContents()
    {
        $holiday_raw = $this->holiday_raws[0];
        $keeper = new UserDtoHandler(UserDtoFactory::createByUid($holiday_raw->keeper_uid));
        if ($keeper === null) {
            throw new \Exception('$keeper === null');
        }
        $request_date = date('Y-m-d');
        $holiday_duration = $this->getHolidayDuration();
        $holiday_sum_cost = $this->getHolidaySumCost();
        $holiday_type = $this->getHolidayType();

        $text = "요청일 : {$request_date}
요청자 : {$this->user->name}
종류 : {$holiday_type}
사용연차 : {$holiday_sum_cost}
사용날짜 : {$holiday_duration}
업무인수인계자 : {$keeper->getName()}
비상시연락처 : {$holiday_raw->phone_emergency}
비고 : {$holiday_raw->memo}
";
        return $text;
    }

    /**
     * @return mixed
     */
    private function getHolidayType()
    {
        $holiday_type = $this->holiday_raws[0]->type;
        return $holiday_type;
    }

    /**
     * @return string
     */
    private function getHolidayDuration()
    {
        if (count($this->holiday_raws) == 1) {
            $date_duration = $this->holiday_raws[0]->date;
            return $date_duration;
        } else {
            $date_durations = [];
            foreach ($this->holiday_raws as $holiday_raw) {
                $date_durations[] = $holiday_raw->date;
            }
            $date_duration = implode(', ', $date_durations);
            return $date_duration;
        }
    }

    /**
     * @return int
     */
    private function getHolidaySumCost()
    {
        $cost_sum = 0;
        foreach ($this->holiday_raws as $holiday_raw) {
            $cost_sum += $holiday_raw->cost;
        }
        return $cost_sum;
    }
}
