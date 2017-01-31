<?php
/** @var $this Intra\Core\Control */
use Intra\Service\User\UserJoinService;

$request = $this->getRequest();
UserJoinService::join($request);
return '1';
