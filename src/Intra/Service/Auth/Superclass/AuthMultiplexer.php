<?php

namespace Intra\Service\Auth\Superclass;

use Intra\Service\User\UserDto;

abstract class AuthMultiplexer
{
    private $must_block_ids = [];
    private $acceptor_ids = [];

    /**
     * @param string[] $ids
     *
     * @return $this
     */
    public function accept($ids)
    {
        $this->acceptor_ids = array_merge($this->acceptor_ids, $ids);

        return $this;
    }

    /**
     * @param string[] $ids
     *
     * @return $this
     */
    public function block($ids)
    {
        $this->must_block_ids = array_merge($this->must_block_ids, $ids);

        return $this;
    }

    public function multiplexingAuth(UserDto $user_dto)
    {
        $id = $user_dto->id;
        if (in_array($id, $this->must_block_ids)) {
            return false;
        }
        if (in_array($id, $this->acceptor_ids)) {
            return true;
        }

        return $this->hasAuth($user_dto);
    }

    /**
     * @param UserDto $user_dto
     *
     * @return bool
     */
    abstract protected function hasAuth(UserDto $user_dto);
}
