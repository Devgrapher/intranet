<?php
/** @var $this Intra\Core\Control */
use Intra\Service\Payment\UserPaymentRequestFilter;
use Intra\Service\Receipt\UserReceiptsStat;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Symfony\Component\HttpFoundation\Response;

if (!UserPolicy::isReceiptsAdmin(UserSession::getSelfDto())) {
    return new Response("권한이 없습니다", 403);
}

$request = $this->getRequest();
$month = $request->get('month');

$month = UserPaymentRequestFilter::parseMonth($month);

$payment_service = new UserReceiptsStat();
return $payment_service->downloadYear($month);
