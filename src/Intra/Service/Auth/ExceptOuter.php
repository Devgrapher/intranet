<?php
namespace Intra\Service\Auth;

use Intra\Service\Auth\Superclass\AuthMultiplexer;
use Intra\Service\User\UserDto;
use Intra\Service\User\UserPolicy;

class ExceptOuter extends AuthMultiplexer
{
    /**
     * @param UserDto $user_dto
     *
     * @return bool
     */
    protected function hasAuth(UserDto $user_dto)
    {
        return !(UserPolicy::isStudioD($user_dto) || UserPolicy::isTa($user_dto));
    }
}
