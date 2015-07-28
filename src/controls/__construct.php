<?php
/** @var $this Intra\Core\Control */
use Intra\Service\UserSession;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

$request = $this->getRequest();

$free_to_login_path = array(
	'/usersession/login',
	'/usersession/login.azure',
	'/users/join',
	'/users/join.ajax',
	'/programs/insert',
	'/programs/list',
);

$is_free_to_login = in_array($request->getPathInfo(), $free_to_login_path);
if (!$is_free_to_login && !UserSession::isLogined()) {
	if ($request->isXmlHttpRequest()) {
		return new Response('login error');
	} else {
		return new RedirectResponse('/usersession/login');
	}
}
