<?php
/** @var $this Intra\Core\Control */
use Intra\Service\Press\Press;
use Intra\Service\User\UserSession;

$request = $this->getRequest();
$page = $request->get('page');
$items_per_page = $request->get('items_per_page');
$user = UserSession::getSelfDto();

$press_service = new Press($user);

return $request->query->get('callback') . '(' . $press_service->getPressByPage($page, $items_per_page) . ')';
