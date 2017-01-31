<?php
/** @var $this Intra\Core\Control */
use Intra\Service\Press\Press;
use Intra\Service\User\UserSession;

$request = $this->getRequest();
$press_id = $request->get('id');
$key = $request->get('key');
$value = $request->get('value');

$user = UserSession::getSelfDto();
$press_service = new Press($user);
return $press_service->edit($press_id, $key, $value);
