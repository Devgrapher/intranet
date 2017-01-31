<?php
/** @var $this Intra\Core\Control */
use Intra\Service\User\UserEditService;
use Intra\Service\User\UserSession;
use Symfony\Component\HttpFoundation\JsonResponse;

$request = $this->getRequest();
$uploadedFile = $request->files->get('files')[0];

$self = UserSession::getSelfDto();
if (!$self) {
    return JsonResponse::create('unknown user', JsonResponse::HTTP_UNAUTHORIZED);
}

$uid = $self->uid;
$savedFile = UserEditService::saveImage($uid, $uploadedFile);
if ($savedFile != null) {
    $thumbFile = UserEditService::createThumb($uid, 180, 180);
    if ($thumbFile != null) {
        if (UserEditService::updateInfo($uid, 'image', '/users/' . $uid . '/image') != null) {
            return JsonResponse::create('success');
        }
    }
}

return JsonResponse::create('file upload failed', JsonResponse::HTTP_SERVICE_UNAVAILABLE);
