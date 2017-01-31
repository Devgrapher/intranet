<?php
/** @var $this Intra\Core\Control */
use Intra\Model\PaymentModel;
use Intra\Service\Payment\PaymentDto;
use Intra\Service\Payment\PaymentDtoFactory;
use Intra\Service\Payment\UserPaymentRequestFilter;
use Intra\Service\Payment\UserPaymentStatService;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Symfony\Component\HttpFoundation\Response;

if (!UserPolicy::isPaymentAdmin(UserSession::getSelfDto())) {
    return new Response("권한이 없습니다", 403);
}

/**
 * @var PaymentDto[]
 */
$request = $this->getRequest();
$requestDateStart = $request->get('request_date_start');
$requestDateEnd = $request->get('request_date_end');
$requestDateStart = UserPaymentRequestFilter::parseDate($requestDateStart);
$requestDateEnd = UserPaymentRequestFilter::parseDate($requestDateEnd);

$payment_service = new UserPaymentStatService();
$user_payment_model = new PaymentModel();
$payments = PaymentDtoFactory::importFromDatabaseDicts($user_payment_model->getAllPaymentsByActiveRequestDate($requestDateStart, $requestDateEnd));
return $payment_service->getCsvRespose($payments);
