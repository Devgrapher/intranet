<?php

namespace Intra\Service\FlexTime;

use Intra\Model\FlexTimeModel;
use Intra\Config\Config;
use Mailgun\Mailgun;

class FlexTimeService
{
	private function getMailReceivers()
	{
//		$holiday_raw = $this->holiday_raws[0];
//		$uids = [$holiday_raw->uid, $holiday_raw->manager_uid, $holiday_raw->keeper_uid];
//		$uids = array_filter(array_unique($uids));
//
//		$users = UserDtoFactory::createDtosByUid($uids);
//
//		$emails = [];
//		foreach ($users as $user) {
//			$emails[] = $user->id . '@' . Config::$domain;
//		}
//		$emails = array_merge($emails, Config::$recipients['holiday']);
//
//		return array_unique(array_filter($emails));

		return [];
	}

	private function sendMail($title, $contents)
	{
		$receivers = $this->getMailReceivers();
		if (Config::$is_dev) {
			if (count(Config::$test_mails)) {
				$receivers = Config::$test_mails;
			} else {
				return true;
			}
		}

		$mg = new Mailgun(Config::$mailgun_api_key);
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

		return $ret;
	}

	public function sendAddMail(FlexTimeModel $flextime)
	{
		$title = 'sendAddMail';
		$content = 'sendAddMail';
		$this->sendMail($title, $content);
	}

	public function sendEditMail(FlexTimeModel $flextime)
	{
		$title = 'sendEditMail';
		$content = 'sendEditMail';
		$this->sendMail($title, $content);
	}

	public function sendDelMail(FlexTimeModel $flextime)
	{
		$title = 'sendDelMail';
		$content = 'sendDelMail';
		$this->sendMail($title, $content);
	}
}
