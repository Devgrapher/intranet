<?php

namespace Intra\Controller;

use DateTime;
use Intra\Service\File\WeeklyScheduleFileService;
use Intra\Service\User\UserSession;
use Intra\Service\Weekly\Weekly;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        $weekly = new Weekly();

        try {
            $weekly->assertPermission($request);

            $now = new DateTime();
            $file_service = new WeeklyScheduleFileService();
            $file_info = $file_service->getLastFile($now->format('W'));
            return new RedirectResponse($file_info['location']);
        } catch (\Exception $e) {
            return Response::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function upload(Request $request, Application $app)
    {
        if ($request->files && $request->files->get("fileToUpload")) {
            $uploadedFile = $request->files->get("fileToUpload");
            if ($uploadedFile) {
                $file_path = $uploadedFile->getRealPath();
                Weekly::dumpToHtml($file_path, $file_path);

                $self = UserSession::getSelfDto();
                $now = new DateTime();
                $file_service = new WeeklyScheduleFileService();
                $file_service->uploadFile(
                    $self->uid,
                    $now->format('W'),
                    pathinfo($file_path, PATHINFO_BASENAME),
                    file_get_contents($file_path),
                    'text/html'
                );
            }
        }

        return $app['twig']->render('weekly/upload.twig', []);
    }
}
