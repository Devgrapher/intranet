<?php

namespace Intra\Controller;

use Intra\Service\IntraDb;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProgramsController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];
        $controller_collection->get('/list', [$this, 'getList']);
        $controller_collection->get('/add/{key}/{value}', [$this, 'add']);
        $controller_collection->get('/insert', [$this, 'insert']);

        return $controller_collection;
    }

    /**
     * @param $programs
     * @return array
     */
    private function getListFromString($programs)
    {
        return preg_split("/\s*\n\s*/U", trim($programs));
    }

    public function getList(Request $request, Application $app)
    {
        $db = IntraDb::getGnfDb();

        $able_programs = $db->sqlDatas('select program from programs');
        $able_fonts = $db->sqlDatas('select font from fonts');

        $program_name_list = [];
        $name_program_list = [];
        $font_name_list = [];
        $name_font_list = [];

        $datas = $db->sqlDicts(
            'select * from (select name, max(`timestamp`) as `timestamp` from userprograms group by name) a natural left join `userprograms` where userprograms.timestamp < ?',
            date('Y/m/d', strtotime('-1 week'))
        );
        foreach ($datas as $data) {
            $name = $data['name'];
            $programs = $data['programs'];
            $fonts = $data['fonts'];

            foreach ($this->getListFromString($programs) as $program) {
                $program_name_list[$program][$name] = true;
                $name_program_list[$name][$program] = true;
            }

            foreach ($this->getListFromString($fonts) as $font) {
                $font_name_list[$font][$name] = true;
                $name_font_list[$name][$font] = true;
            }
        }

        ksort($program_name_list);
        ksort($able_programs);

        return $app['twig']->render('programs/list.twig', [
            'able_programs' => $able_programs,
            'able_fonts' => $able_fonts,
            'program_name_list' => $program_name_list,
            'name_program_list' => $name_program_list,
            'font_name_list' => $font_name_list,
            'name_font_list' => $name_font_list,
        ]);
    }

    public function add(Request $request, Application $app)
    {
        $key = $request->get('key');
        $value = $request->get('value');
        $value = urldecode($value);
        var_dump($value);

        $db = IntraDb::getGnfDb();
        $insert = [
            $key => $value
        ];
        if ($db->sqlInsert($key . 's', $insert)) {
            return Response::create("추가 되었습니다", Response::HTTP_OK);
        } else {
            return Response::create("추가 되지않았습니다", Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function insert(Request $request, Application $app)
    {
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

        return Response::create("프로그램과 폰트목록이 확인되었습니다", Response::HTTP_OK);
    }
}
