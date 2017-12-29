<?php

namespace Intra\Controller;

use Intra\Service\IntraDb;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class APIController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];
        $controller_collection->get('/ridibooks_ids', [$this, 'getRidibooksIds']);

        return $controller_collection;
    }

    public function getRidibooksIds(Request $request, Application $app)
    {
        $db = IntraDb::getGnfDb();
        $ridi_ids = $db->sqlDatas("select ridibooks_id from users where off_date > '2038-01-01' and ridibooks_id is not null");

        return JsonResponse::create($ridi_ids)->getContent();
    }
}
