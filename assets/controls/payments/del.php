<?php
/** @var $this Intra\Core\Control */
use Intra\Service\Payment\UserPaymentService;
use Intra\Service\User\UserSession;

$request = $this->getRequest();
$paymentid = $request->get('paymentid');

$payment_service = new UserPaymentService(UserSession::getSelfDto());
$row = $payment_service->getRowService($paymentid);
return $row->del();
