<?php
/** @var $this Intra\Core\Control */
use Intra\Service\User\UserDto;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserDtoHandler;
use Intra\Service\User\UserSession;
use Intra\Service\User\UserType;

if (!UserSession::isUserManager()) {
    return '권한이 없습니다';
}

$user_dtos = UserDtoFactory::createAllUserDtos();

if ($this->getRequest()->get('outer')) {
    $user_dtos = array_filter($user_dtos, function (UserDto $item) {
        $type = (new UserDtoHandler($item))->getType();
        return $type == UserType::OUTER;
    });
} else {
    $user_dtos = array_filter($user_dtos, function (UserDto $item) {
        $type = (new UserDtoHandler($item))->getType();
        return $type != UserType::OUTER;
    });
}

return [
    'users' => $user_dtos,
];
