<?php
/** @var $this Intra\Core\Control */
use Intra\Service\IntraDb;

$request = $this->getRequest();

if ($request->get('urlencode_data')) {
    $urlencode_data = $request->get('urlencode_data');

    $dara_raw = urldecode($urlencode_data);
    $dara_raw = str_replace("\x00", "", $dara_raw);
    parse_str($dara_raw, $data);

    $post_data = [
        'name' => $data['name'],
        'computer_name' => $data['computer_name'],
        'programs' => $data['programs'],
        'fonts' => $data['fonts'],
        'ip' => $data['ip'],
    ];
} else {
    $post_data = [
        'name' => $request->get('name'),
        'computer_name' => $request->get('computer_name'),
        'programs' => $request->get('programs'),
        'fonts' => $request->get('fonts'),
        'ip' => $request->get('ip'),
    ];
}

$programs_insert = $post_data;

$db = IntraDb::getGnfDb();
$db->sqlInsert('userprograms', $programs_insert);

return "프로그램과 폰트목록이 확인되었습니다";
