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
$day = $request->get('day');
$title = $request->get('title');
$scope = $request->get('scope');
$type = $request->get('type');
$cost = $request->get('cost');
$payment = $request->get('payment');
$note = $request->get('note');

$uid = $request->get('uid');
if (!intval($uid) || !UserPolicy::isReceiptsAdmin($self)) {
    $uid = $self->uid;
}

$user_dto_object = new UserDtoHandler(UserDtoFactory::createByUid($uid));
$target_user_dto = $user_dto_object->exportDto();

$payment_service = new UserReceipts($target_user_dto);
return $payment_service->add($month, $day, $title, $scope, $type, $cost, $payment, $note);
