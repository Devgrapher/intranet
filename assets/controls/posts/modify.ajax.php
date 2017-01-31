<?php
/** @var $this Intra\Core\Control */
use Intra\Core\JsonDto;
use Intra\Service\Post\Post;
use Symfony\Component\HttpFoundation\JsonResponse;

$jsonDto = new JsonDto();
try {
    $request = $this->getRequest();
    $post = new Post();
    $post->modify($request);
    $jsonDto->setMsg('수정되었습니다.');
} catch (Exception $e) {
    $jsonDto->setException($e);
}

return JsonResponse::create($jsonDto);
