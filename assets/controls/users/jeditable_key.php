<?php
/** @var $this Intra\Core\Control */
use Intra\Service\User\UserConstant;
use Symfony\Component\HttpFoundation\JsonResponse;

$request = $this->getRequest();
$key = $request->get('key');

if (UserConstant::$jeditable_key_list[$key]) {
    $values = UserConstant::$jeditable_key_list[$key];
    $dicts = [];
    foreach ($values as $value) {
        $dicts[$value] = $value;
    }
    return JsonResponse::create($dicts);
}
