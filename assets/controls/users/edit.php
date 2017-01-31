<?php
/** @var $this Intra\Core\Control */
use Intra\Service\User\UserEditService;
use Symfony\Component\HttpFoundation\Response;

$request = $this->getRequest();
$uid = $request->get('uid');
$key = $request->get('key');
$value = $request->get('value');

if (UserEditService::updateInfo($uid, $key, $value) !== null) {
    return Response::create($value, Response::HTTP_OK);
} else {
    return Response::create("server error", Response::HTTP_SERVICE_UNAVAILABLE);
}
