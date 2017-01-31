<?php
/** @var $this Intra\Core\Control */
use Intra\Service\Payment\PaymentDto;
use Intra\Service\Payment\UserPaymentService;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserDtoHandler;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;

$request = $this->getRequest();
$self = UserSession::getSelfDto();

$uid = $request->get('uid');
if (!intval($uid) || !UserPolicy::isPaymentAdmin($self)) {
    $uid = $self->uid;
}

$payment_dto = PaymentDto::importFromAddRequest($request, $uid, UserPolicy::isPaymentAdmin($self));

$user_dto_instancce = new UserDtoHandler(UserDtoFactory::createByUid($uid));
$target_user_dto = $user_dto_instancce->exportDto();

$payment_service = new UserPaymentService($target_user_dto);
return $payment_service->add($payment_dto);
