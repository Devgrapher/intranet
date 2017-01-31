<?php
/** @var $this Intra\Core\Control */
use Intra\Core\MsgException;
use Intra\Service\Payment\UserPaymentService;
use Intra\Service\User\UserSession;
use Symfony\Component\HttpFoundation\Response;

$request = $this->getRequest();
$self = UserSession::getSelfDto();

$fileid = $request->get('fileid');
if (!intval($fileid)) {
    throw new MsgException("invalid fileid");
}

if (UserPaymentService::deleteFile($self, $fileid)) {
    return Response::create('1');
} else {
    return Response::create('삭제실패했습니다.');
}
