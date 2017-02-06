<?php

namespace Intra\Controller;

use Intra\Model\LightFileModel;
use Intra\Service\Ridi;
use Intra\Service\User\UserSession;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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

    public function getChart(Request $request, Application $app)
    {
        if (!Ridi::isRidiIP() || UserSession::isTa()) {
            return Response::create('권한이 없습니다.', Response::HTTP_UNAUTHORIZED);
        }

        $filebag = new LightFileModel('organization');

        return BinaryFileResponse::create($filebag->getLocation('recent'));
    }

    public function upload(Request $request, Application $app)
    {
        if ($request->files && $request->files->get("fileToUpload")) {
            $uploadedFile = $request->files->get("fileToUpload");
            $infile = $uploadedFile->getRealPath();

            if ($infile) {
                $filebag = new LightFileModel('organization');
                $outfile = $filebag->getLocation(date("Y-m-d") . ".pdf");

                if (!move_uploaded_file($infile, $outfile)) {
                    return Response::create('파일을 업로드 하지 못했습니다.', Response::HTTP_INTERNAL_SERVER_ERROR);
                }

                $recent = $filebag->getLocation('recent');

                unlink($recent);
                symlink($outfile, $recent);
            }
        }

        return $app['twig']->render('organization/upload.twig', []);
    }
}
