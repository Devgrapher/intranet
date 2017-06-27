<?php

namespace Intra\Controller;

use DateTime;
use Intra\Service\File\OrganizationFileService;
use Intra\Service\Ridi;
use Intra\Service\User\UserSession;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class OrganizationController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];
        $controller_collection->get('/chart', [$this, 'getChart']);
        $controller_collection->match('/upload', [$this, 'upload'])->method('GET|POST');
        return $controller_collection;
    }

    public function getChart(Request $request)
    {
        if (!Ridi::isRidiIP($request->getClientIp()) || UserSession::isTa()) {
            return Response::create('권한이 없습니다.', Response::HTTP_UNAUTHORIZED);
        }

        $now = new DateTime();
        $file_service = new OrganizationFileService();
        $file_info = $file_service->getLastFile($now->format('W'));

        return new RedirectResponse($file_info['location']);
    }

    public function upload(Request $request, Application $app)
    {
        if ($request->files && $request->files->get("fileToUpload")) {
            $uploadedFile = $request->files->get("fileToUpload");

            $self = UserSession::getSelfDto();
            $now = new DateTime();
            $file_service = new OrganizationFileService();
            $file_service->uploadFile(
                $self->uid,
                $now->format('W'),
                $uploadedFile->getClientOriginalName(),
                file_get_contents($uploadedFile->getRealPath()),
                'application/pdf'
            );
        }

        return $app['twig']->render('organization/upload.twig', []);
    }
}
