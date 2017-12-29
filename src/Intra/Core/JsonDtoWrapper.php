<?php

namespace Intra\Core;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonDtoWrapper
{
    /**
     * @param callable $function
     *
     * @return JsonResponse
     */
    public static function create(callable $function)
    {
        try {
            $json_dto = $function();
            if (!$json_dto instanceof JsonDto) {
                $json_dto = new JsonDto('실패했습니다. 플랫폼팀에 오류내용이 전달되었습니다.');
                $json_dto->success = false;
            }
        } catch (MsgException $e) {
            $json_dto = new JsonDto($e->getMessage());
            $json_dto->success = false;
        } catch (\Exception $e) {
            $json_dto = new JsonDto($e->getMessage());
            $json_dto->success = false;
        }

        return new JsonResponse($json_dto);
    }
}
