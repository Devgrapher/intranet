<?php
/** @var $this Intra\Core\Control */
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;

$self = UserSession::getSelfDto();
$replaceable = UserPolicy::isFirstPageEditable($self);

return [
    'replaceable' => $replaceable,
    'users' => UserDtoFactory::createAvailableUserDtos(),
    'allUsers' => UserDtoFactory::createAllUserDtos(),
    'allCurrentUsers' => UserDtoFactory::createAvailableUserDtos()
];
