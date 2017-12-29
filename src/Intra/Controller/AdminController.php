<?php
declare(strict_types=1);

namespace Intra\Controller;

use Intra\Service\Mail\MailRecipient;
use Intra\Service\Room\RoomService;
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
        $controller_collection->get('/room', [$this, 'getRoom']);
        $controller_collection->post('/room', [$this, 'postRoom']);
        $controller_collection->get('/event_group', [$this, 'getEventGroup']);
        $controller_collection->post('/event_group', [$this, 'postEventGroup']);
        $controller_collection->post('/event_group/{id}', [$this, 'postEventGroup']);
        $controller_collection->delete('/event_group/{id}', [$this, 'deleteEventGroup']);

        return $controller_collection;
    }

    public function getPolicy(Request $request, Application $app)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isPolicyRecipientEditable($self)) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            return $app->json(UserPolicy::getAllWithUsers());
        }

        return $app['twig']->render('admin/policy/index.twig');
    }

    public function postPolicy(Request $request)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isPolicyRecipientEditable($self)) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        UserPolicy::setAll($data['assigned']);

        return new Response('success', Response::HTTP_OK);
    }

    public function getRecipient(Request $request, Application $app)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isPolicyRecipientEditable($self)) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            return $app->json(MailRecipient::getAllWithUsers());
        }

        return $app['twig']->render('admin/recipient/index.twig');
    }

    public function postRecipient(Request $request)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isPolicyRecipientEditable($self)) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        MailRecipient::setAll($data['assigned']);

        return new Response('success', Response::HTTP_OK);
    }

    public function getRoom(Request $request, Application $app)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isPolicyRecipientEditable($self)) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            return $app->json(RoomService::getAllRoomSections());
        }

        return $app['twig']->render('admin/room/index.twig');
    }

    public function postRoom(Request $request)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isPolicyRecipientEditable($self)) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        RoomService::setAll($data['assigned']);

        return new Response('success', Response::HTTP_OK);
    }

    public function getEventGroup(Request $request, Application $app)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isPolicyRecipientEditable($self)) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            return $app->json(RoomService::getAllEventgroups());
        }

        return $app['twig']->render('admin/event_group/index.twig');
    }

    public function postEventGroup(Request $request, Application $app)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isPolicyRecipientEditable($self)) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $event_group_id = intval($request->get('id'));
        $data = json_decode($request->getContent(), true);

        if ($event_group_id) {
            $result = RoomService::editEventGroup($event_group_id, $data);
        } else {
            $result = RoomService::addEventGroup($data);
        }

        return $app->json($result);
    }

    public function deleteEventGroup(Request $request)
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isPolicyRecipientEditable($self)) {
            return Response::create('unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $event_group_id = intval($request->get('id'));

        RoomService::deleteEventGroup($event_group_id);

        return new Response('success', Response::HTTP_OK);
    }
}
