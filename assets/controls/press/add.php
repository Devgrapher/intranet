<?php
/** @var $this Intra\Core\Control */
use Intra\Service\Press\Press;
use Intra\Service\User\UserSession;

$request = $this->getRequest();

$date = $request->get('date');
$media = $request->get('media');
$title = $request->get('title');
$link_url = $request->get('link_url');
$note = $request->get('note');
$user = UserSession::getSelfDto();
$press_service = new Press($user);
return $press_service->add($date, $media, $title, $link_url, $note);
