<?php
/** @var $this Intra\Core\Control */
use Intra\Service\Payment\UserPaymentRequestFilter;

$request = $this->getRequest();
$pay_type_str = $request->get('pay_type_str');

return UserPaymentRequestFilter::getPayDateByStr($pay_type_str);
