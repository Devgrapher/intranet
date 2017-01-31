<?php
/** @var $this Intra\Core\Control */
use Intra\Service\User\UserEditService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

$request = $this->getRequest();
$uid = $request->get('uid');

$file = UserEditService::getThumbLocation($uid);
if ($file !== null) {
    return BinaryFileResponse::create($file, Response::HTTP_OK);
} else {
    return Response::create('file not exist', Response::HTTP_NOT_FOUND);
}
