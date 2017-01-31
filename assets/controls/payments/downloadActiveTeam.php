<?php
/** @var $this Intra\Core\Control */
use Intra\Model\PaymentModel;
use Intra\Service\Payment\PaymentDto;
use Intra\Service\Payment\PaymentDtoFactory;
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
$team = $request->get('team');

$payment_service = new UserPaymentStatService();
$user_payment_model = new PaymentModel();
$payments = PaymentDtoFactory::importFromDatabaseDicts($user_payment_model->getAllPaymentsByActiveTeam($team));
return $payment_service->getCsvRespose($payments);
