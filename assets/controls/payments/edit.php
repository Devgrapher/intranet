<?php
/** @var $this Intra\Core\Control */
use Intra\Service\Payment\UserPaymentService;
use Intra\Service\User\UserSession;

$request = $this->getRequest();
$paymentid = $request->get('paymentid');
$key = $request->get('key');
$value = $request->get('value');

$payment_service = new UserPaymentService(UserSession::getSelfDto());
$row = $payment_service->getRowService($paymentid);
if ($key == 'is_manager_accepted') {
    return $row->acceptManageer();
} elseif ($key == 'is_co_accepted') {
    return $row->acceptCO();
} else {
    return $row->edit($key, $value);
}
