<?php

namespace Intra\Controller;

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
        $controller_collection->match('/upload', [$this, 'upload'])->method('GET|POST');
        return $controller_collection;
    }

    public function index(Request $request)
    {
        try {
            Weekly::assertPermission($request);
            return Weekly::getContents();
        } catch (\Exception $e) {
            return Response::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upload(Request $request, Application $app)
    {
        if ($request->files && $request->files->get("fileToUpload")) {
            $uploadedFile = $request->files->get("fileToUpload");
            if ($uploadedFile) {
                Weekly::upload($uploadedFile);
            }
        }

        return $app['twig']->render('weekly/upload.twig');
    }
}
