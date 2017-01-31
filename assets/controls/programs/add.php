<?php
/** @var $this Intra\Core\Control */
use Intra\Service\IntraDb;

$request = $this->getRequest();

$key = $request->get('key');
$value = $request->get('value');
$value = urldecode($value);
var_dump($value);

$db = IntraDb::getGnfDb();
$insert = [
    $key => $value
];
if ($db->sqlInsert($key . 's', $insert)) {
    return "추가 되었습니다";
} else {
    return "추가 되지않았습니다";
}
