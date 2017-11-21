<?php
namespace Intra\Service\Auth;

use Intra\Service\Auth\Superclass\AuthMultiplexer;
use Intra\Service\User\UserDto;
use Intra\Service\User\UserPolicy;

class OnlyPolicyRecipientEditable extends AuthMultiplexer
{
    protected function hasAuth(UserDto $user_dto): bool
    {
        return UserPolicy::isPolicyRecipientEditable($user_dto);
    }
}
