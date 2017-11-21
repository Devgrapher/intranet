<?php
declare(strict_types=1);

namespace Intra\Controller;

use Intra\Service\Mail\MailRecipient;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];
        $controller_collection->get('/policy', [$this, 'getPolicy']);
        $controller_collection->post('/policy', [$this, 'postPolicy']);
        $controller_collection->get('/recipient', [$this, 'getRecipient']);
        $controller_collection->post('/recipient', [$this, 'postRecipient']);
        return $controller_collection;
    }

    public function getPolicy(Request $request, Application $app)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isSuperAdmin($self)) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            return $app->json(UserPolicy::getAllWithUsers());
        }

        return $app['twig']->render('admin/policy/index.twig');
    }

    public function postPolicy(Request $request, Application $app)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isSuperAdmin($self)) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        UserPolicy::setAll($data['assigned']);

        return new Response('success', Response::HTTP_OK);
    }

    public function getRecipient(Request $request, Application $app)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isSuperAdmin($self)) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            return $app->json(MailRecipient::getAllWithUsers());
        }

        return $app['twig']->render('admin/recipient/index.twig');
    }

    public function postRecipient(Request $request, Application $app)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isSuperAdmin($self)) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        MailRecipient::setAll($data['assigned']);

        return new Response('success', Response::HTTP_OK);
    }
}
