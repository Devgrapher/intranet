<?php
/** @var $this Intra\Core\Control */
use Intra\Service\Receipt\UserReceipts;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserDtoHandler;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;

$request = $this->getRequest();
$self = UserSession::getSelfDto();

$month = $request->get('month');
$month = UserReceipts::parseMonth($month);
$uid = $request->get('uid');

if (!intval($uid) || !UserPolicy::isReceiptsAdmin($self)) {
    $uid = $self->uid;
}

$user_dto_object = new UserDtoHandler(UserDtoFactory::createByUid($uid));
$target_user_dto = $user_dto_object->exportDto();
$payment_service = new UserReceipts($target_user_dto);
return $payment_service->index($month);
