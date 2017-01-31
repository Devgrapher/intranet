<?php
/** @var $this Intra\Core\Control */
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserDtoHandler;

$request = $this->getRequest();

$uid = $request->get('userid');
$key = $request->get('key');
$value = rawurldecode($request->get('value'));

$user = new UserDtoHandler(UserDtoFactory::createByUid($uid));
$user->setExtra($key, $value);
return 1;
