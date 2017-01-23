<?php
/** @var $this Intra\Core\Control */
use Intra\Service\Press\Press;
use Intra\Service\User\UserSession;

$request = $this->getRequest();
$user = UserSession::getSelfDto();

$press_service = new Press($user);
return [
    'user' => $user,
    'press' => $press_service->getAll(),
    'manager' => UserSession::isPressManager(),
];
