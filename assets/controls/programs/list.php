<?php
/** @var $this Intra\Core\Control */
$db = \Intra\Service\IntraDb::getGnfDb();

$able_programs = $db->sqlDatas('select program from programs');
$able_fonts = $db->sqlDatas('select font from fonts');

$program_name_list = [];
$name_program_list = [];
$font_name_list = [];
$name_font_list = [];

/**
 * @param $programs
 * @return array
 */
function getListFromString($programs)
{
    return preg_split("/\s*\n\s*/U", trim($programs));
}

$datas = $db->sqlDicts(
    'select * from (select name, max(`timestamp`) as `timestamp` from userprograms group by name) a natural left join `userprograms` where userprograms.timestamp < ?',
    date('Y/m/d', strtotime('-1 week'))
);
foreach ($datas as $data) {
    $name = $data['name'];
    $programs = $data['programs'];
    $fonts = $data['fonts'];

    foreach (getListFromString($programs) as $program) {
        $program_name_list[$program][$name] = true;
        $name_program_list[$name][$program] = true;
    }

    foreach (getListFromString($fonts) as $font) {
        $font_name_list[$font][$name] = true;
        $name_font_list[$name][$font] = true;
    }
}

ksort($program_name_list);
ksort($able_programs);

return compact(
    'able_programs',
    'able_fonts',
    'program_name_list',
    'name_program_list',
    'font_name_list',
    'name_font_list'
);
