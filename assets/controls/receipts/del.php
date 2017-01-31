<?php
/** @var $this Intra\Core\Control */
use Intra\Service\Receipt\UserReceipts;
use Intra\Service\User\UserSession;

$request = $this->getRequest();
$receiptid = $request->get('receiptid');
$self = UserSession::getSelfDto();

$payment_service = new UserReceipts($self);
return $payment_service->del($receiptid);
