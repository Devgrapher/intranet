<?php
/** @var $this Intra\Core\Control */
use Intra\Service\Payment\UserPaymentConst;

$request = $this->getRequest();
$key = $request->get('key');

return UserPaymentConst::getConstValueByKey($key);
