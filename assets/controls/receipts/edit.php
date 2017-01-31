<?php
/** @var $this Intra\Core\Control */
use Intra\Service\Receipt\UserReceipts;
use Intra\Service\User\UserSession;

$request = $this->getRequest();
$receiptid = $request->get('receiptid');
$key = $request->get('key');
$value = $request->get('value');

$user = UserSession::getSelfDto();
$payment_service = new UserReceipts($user);
return $payment_service->edit($receiptid, $key, $value);
