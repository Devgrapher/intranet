<?php
declare(strict_types=1);

namespace Intra\Controller\Admin;

use Intra\Service\Mail\MailRecipient;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RecipientController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];

        $controller_collection->get('/', [$this, 'get']);
        $controller_collection->post('/', [$this, 'post']);

        $controller_collection->before(function () {
            if (!UserPolicy::isPolicyRecipientEditable(UserSession::getSelfDto())) {
                return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
            }
        });

        return $controller_collection;
    }

    public function get(Request $request, Application $app)
    {
        if (!in_array('application/json', $request->getAcceptableContentTypes())) {
            return $app['twig']->render('admin/recipient/index.twig');
        }

        return $app->json(MailRecipient::getAllWithUsers());
    }

    public function post(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        MailRecipient::setAll($data['assigned']);

        return new Response('success', Response::HTTP_OK);
    }
}
