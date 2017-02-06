<?php

namespace Intra\Controller;

use Intra\Model\LightFileModel;
use Intra\Service\Weekly\Weekly;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WeeklyController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];
        $controller_collection->get('/', [$this, 'index']);
        $controller_collection->get('/upload', [$this, 'upload']);
        return $controller_collection;
    }

    public function index(Request $request, Application $app)
    {
        $weekly = new Weekly();

        try {
            $weekly->assertPermission();
            return $app['twig']->render('weekly/index.twig', ['html' => $weekly->getContents()]);
        } catch (\Exception $e) {
            return Response::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upload(Request $request, Application $app)
    {
        $infile = $_FILES["fileToUpload"]["tmp_name"];
        $filebag = new LightFileModel('weekly');
        $filename = Weekly::getFilename();
        $outfile = $filebag->getLocation($filename);

        if ($infile) {
            Weekly::dumpToHtml($infile, $outfile);
        }

        return $app['twig']->render('weekly/index.twig', []);
    }
}
