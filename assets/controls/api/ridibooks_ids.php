<?php
/** @var $this Intra\Core\Control */
use Intra\Service\IntraDb;
use Symfony\Component\HttpFoundation\JsonResponse;

$db = IntraDb::getGnfDb();
$ridi_ids = $db->sqlDatas("select ridibooks_id from users where off_date > '2038-01-01' and ridibooks_id is not null");

return JsonResponse::create($ridi_ids)->getContent();
