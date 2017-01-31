<?php
/** @var $this Intra\Core\Control */
use Intra\Core\JsonDto;
use Intra\Service\Post\Post;
use Symfony\Component\HttpFoundation\JsonResponse;

$jsonDto = new JsonDto();
try {
    $request = $this->getRequest();
    $post = new Post();
    if ($post->del($request)) {
        $jsonDto->setMsg('삭제되었습니다.');
    } else {
        $jsonDto->success = 0;
        $jsonDto->setMsg('삭제가 되지 않았습니다. 플랫폼팀에 문의해주세요');
    }
} catch (Exception $e) {
    $jsonDto->setException($e);
}

return JsonResponse::create($jsonDto);
