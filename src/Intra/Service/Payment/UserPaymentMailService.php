<?php
namespace Intra\Service\Payment;

use Intra\Config\Config;
use Intra\Core\Application;
use Intra\Service\User\UserConstant;
use Intra\Service\User\UserService;
use Mailgun\Mailgun;

class UserPaymentMailService
{
	public static function sendMail($type, $payment_id)
	{
		$payment_dto = PaymentDtoFactory::createFromDatabaseByPk($payment_id);
		list($title, $html, $receivers) = self::getMailContents($type, $payment_dto);
		self::sendMailRaw($receivers, $title, $html);
	}

	/**
	 * @param $type
	 * @param $dto
	 *
	 * @return array
	 */
	private function getMailContents($type, PaymentDto $dto)
	{
		$title = "[{$type}][{$dto->team}][{$dto->month}] {$dto->register_name}님의 요청, {$dto->category}";
		$html = Application::$view->render('payments/template/add', ['item' => $dto]);
		$receivers = [
			UserService::getEmailByUidSafe($dto->uid),
			UserService::getEmailByUidSafe($dto->manager_uid)
		];
		if ($dto->category == UserPaymentConst::CATEGORY_USER_BOOK_CANCELMENT) {
			$receivers_append = UserService::getEmailsByTeam(UserConstant::TEAM_CCPQ);
			$receivers = array_merge($receivers, $receivers_append);
			$receivers = array_unique($receivers);
		}
		if ($dto->category == UserPaymentConst::CATEGORY_USER_DEVICE_CANCELMENT) {
			$receivers_append = UserService::getEmailsByTeam(UserConstant::TEAM_DEVICE);
			$receivers = array_merge($receivers, $receivers_append);
			$receivers = array_unique($receivers);
		}
		return [$title, $html, $receivers];
	}

	/**
	 * @param $receivers
	 * @param $title
	 * @param $html
	 */
	private function sendMailRaw($receivers, $title, $html)
	{
		$receivers = array_merge($receivers, Config::$recipients['payment']);

		if (Config::$is_dev) {
			if (strlen(Config::$test_mail)) {
				$receivers = [Config::$test_mail];
			} else {
				return;
			}
		}

		$mg = new Mailgun("***REMOVED***");
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
