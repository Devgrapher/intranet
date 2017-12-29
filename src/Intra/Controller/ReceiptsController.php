<?php

namespace Intra\Controller;

use Intra\Service\Payment\UserPaymentRequestFilter;
use Intra\Service\Receipt\UserReceipts;
use Intra\Service\Receipt\UserReceiptsStat;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserDtoHandler;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ReceiptsController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];
        $controller_collection->get('/', [$this, 'index']);
        $controller_collection->get('/uid/{uid}', [$this, 'index']);
        $controller_collection->get('/uid/{uid}/month/{month}', [$this, 'index']);
        $controller_collection->post('/uid/{uid}', [$this, 'add']);
        $controller_collection->post('/receiptid/{receiptid}', [$this, 'edit']);
        $controller_collection->delete('/receiptid/{receiptid}', [$this, 'del']);
        $controller_collection->get('/download/{month}', [$this, 'download']);
        $controller_collection->get('/downloadYear/{month}', [$this, 'downloadYear']);
        $controller_collection->post('/queryIsWeekend', [$this, 'queryIsWeekend']);

        return $controller_collection;
    }

    public function index(Request $request, Application $app)
    {
        $self = UserSession::getSelfDto();

        $month = $request->get('month');
        $month = UserReceipts::parseMonth($month);
        $uid = $request->get('uid');

        if (!intval($uid) || !UserPolicy::isReceiptsAdmin($self)) {
            $uid = $self->uid;
        }

        $user_dto_object = new UserDtoHandler(UserDtoFactory::createByUid($uid));
        $target_user_dto = $user_dto_object->exportDto();
        $payment_service = new UserReceipts($target_user_dto);
        $twig_param = $payment_service->index($month);
        $twig_param['isAdmin'] = UserPolicy::isReceiptsAdmin(UserSession::getSelfDto());

        return $app['twig']->render('receipts/index.twig', $twig_param);
    }

    public function add(Request $request, Application $app)
    {
        try {
            $self = UserSession::getSelfDto();

            $month = $request->get('month');
            $day = $request->get('day');
            $title = $request->get('title');
            $scope = $request->get('scope');
            $type = $request->get('type');
            $cost = $request->get('cost');
            $payment = $request->get('payment');
            $note = $request->get('note');

            $uid = $request->get('uid');
            if (!intval($uid) || !UserPolicy::isReceiptsAdmin($self)) {
                $uid = $self->uid;
            }

            $user_dto_object = new UserDtoHandler(UserDtoFactory::createByUid($uid));
            $target_user_dto = $user_dto_object->exportDto();

            $payment_service = new UserReceipts($target_user_dto);
            $result = $payment_service->add($month, $day, $title, $scope, $type, $cost, $payment, $note);
            if ($result == 1) {
                return Response::create('success', Response::HTTP_OK);
            } else {
                return Response::create($result, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            return Response::create($e->getMessage(), Response::HTTP_OK);
        }
    }

    public function edit(Request $request, Application $app)
    {
        $receiptid = $request->get('receiptid');
        $key = $request->get('key');
        $value = $request->get('value');

        $user = UserSession::getSelfDto();
        $payment_service = new UserReceipts($user);
        $result = $payment_service->edit($receiptid, $key, $value);
        if ($result) {
            return Response::create($result, Response::HTTP_OK);
        } else {
            return Response::create('fail', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function del(Request $request, Application $app)
    {
        try {
            $receiptid = $request->get('receiptid');
            $self = UserSession::getSelfDto();

            $payment_service = new UserReceipts($self);
            $result = $payment_service->del($receiptid);
            if ($result == 1) {
                return Response::create('success', Response::HTTP_OK);
            } else {
                return Response::create($result, Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            return Response::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function queryIsWeekend(Request $request, Application $app)
    {
        $month = $request->get('month');
        $day = $request->get('day');

        $result = UserReceipts::queryWeekend($month, $day);

        return Response::create($result, 200);
    }

    public function download(Request $request, Application $app)
    {
        if (!UserPolicy::isReceiptsAdmin(UserSession::getSelfDto())) {
            return new Response("권한이 없습니다", 403);
        }

        $month = $request->get('month');

        $month = UserPaymentRequestFilter::parseMonth($month);
        $payment_service = new UserReceiptsStat();

        return $payment_service->download($month);
    }

    public function downloadYear(Request $request, Application $app)
    {
        if (!UserPolicy::isReceiptsAdmin(UserSession::getSelfDto())) {
            return new Response("권한이 없습니다", 403);
        }

        $month = $request->get('month');

        $month = UserPaymentRequestFilter::parseMonth($month);

        $payment_service = new UserReceiptsStat();

        return $payment_service->downloadYear($month);
    }
}
