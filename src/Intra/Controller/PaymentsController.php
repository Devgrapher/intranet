<?php

namespace Intra\Controller;

use Intra\Model\PaymentModel;
use Intra\Service\File\PaymentFileService;
use Intra\Service\Payment\PaymentDto;
use Intra\Service\Payment\PaymentDtoFactory;
use Intra\Service\Payment\UserPaymentConst;
use Intra\Service\Payment\UserPaymentMailService;
use Intra\Service\Payment\UserPaymentRequestFilter;
use Intra\Service\Payment\UserPaymentService;
use Intra\Service\Payment\UserPaymentStatService;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserDtoHandler;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentsController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controller_collection = $app['controllers_factory'];

        $controller_collection->get('/', [$this, 'index']);
        $controller_collection->get('/uid/{uid}/month/{month}', [$this, 'index']);
        $controller_collection->get('/uid/{uid}', [$this, 'index']);
        $controller_collection->get('/remain', [$this, 'index'])->value('type', 'remain');
        $controller_collection->get('/today', [$this, 'index'])->value('type', 'today');
        $controller_collection->get('/today/confirmed', [$this, 'index'])->value('type', 'todayConfirmed');
        $controller_collection->get('/today/unconfirmed', [$this, 'index'])->value('type', 'todayUnconfirmed');
        $controller_collection->get('/month', [$this, 'index'])->value('type', 'month');

        $controller_collection->post('/uid/{uid}', [$this, 'add']);
        $controller_collection->match('/paymentid/{paymentid}', [$this, 'edit'])->method('PUT|PATCH|POST');
        $controller_collection->delete('/paymentid/{paymentid}', [$this, 'del']);

        $controller_collection->get('/const/{key}', [$this, 'getConst']);

        $controller_collection->get('/download/{month}', [$this, 'download']);
        $controller_collection->post('/downloadActiveCategory', [$this, 'downloadActiveCategory']);
        $controller_collection->post('/downloadActiveMonth', [$this, 'downloadActiveMonth']);
        $controller_collection->post('/downloadActiveRequestDate', [$this, 'downloadActiveRequestDate']);
        $controller_collection->post('/downloadActiveTeam', [$this, 'downloadActiveTeam']);
        $controller_collection->post('/downloadTodayBankTransfer', [$this, 'downloadTodayBankTransfer']);
        $controller_collection->get('/downloadRemain/{month}', [$this, 'downloadRemain']);
        $controller_collection->post('/downloadTaxDate', [$this, 'downloadTaxDate']);

        $controller_collection->get('/file/{fileid}', [$this, 'downloadFile']);
        $controller_collection->delete('/file/{fileid}', [$this, 'deleteFile']);
        $controller_collection->post('/file_upload', [$this, 'uploadFile']);

        $controller_collection->post('/get_pay_date_by_str', [$this, 'getPayDateByStr']);

        return $controller_collection;
    }

    public function index(Request $request, Application $app)
    {
        try {
            $self = UserSession::getSelfDto();

            $uid = $request->get('uid');
            if (!intval($uid) || !UserPolicy::isPaymentAdmin($self)) {
                $uid = $self->uid;
            }
            $month = $request->get('month');
            if (!strlen($month)) {
                $month = date('Y-m');
            }
            $type = ($request->get('type'));
            $month = UserPaymentRequestFilter::parseMonth($month);
            $params = $request->query->all();

            $user_dto_object = new UserDtoHandler(UserDtoFactory::createByUid($uid));
            $target_user_dto = $user_dto_object->exportDto();

            $payment_service = new UserPaymentService($target_user_dto);
            $data = $payment_service->index($month, $type, $params);

            if (in_array('application/json', $request->getAcceptableContentTypes())) {
                return JsonResponse::create($data);
            }

            if (in_array('text/csv', $request->getAcceptableContentTypes())) {
                $payment_stat_service = new UserPaymentStatService();
                if (isset($params['bankTransferOnly']) && $params['bankTransferOnly']) {
                    return $payment_stat_service->getBankTransferCsvRespose($data['payments']);
                }
                return $payment_stat_service->getCsvRespose($data['payments']);
            }

            return $app['twig']->render('payments/index.twig', $data);
        } catch (\Exception $e) {
            return Response::create($e->getMessage(), Response::HTTP_OK);
        }
    }

    public function add(Request $request, Application $app)
    {
        try {
            $self = UserSession::getSelfDto();

            $uid = $request->get('uid');
            if (!intval($uid) || !UserPolicy::isPaymentAdmin($self)) {
                $uid = $self->uid;
            }

            $payment_dto = PaymentDto::importFromAddRequest($request, $uid, UserPolicy::isPaymentAdmin($self));

            $user_dto_instancce = new UserDtoHandler(UserDtoFactory::createByUid($uid));
            $target_user_dto = $user_dto_instancce->exportDto();

            $payment_service = new UserPaymentService($target_user_dto);
            $insert_id = $payment_service->add($payment_dto);
            if ($insert_id != null) {
                UserPaymentMailService::sendMail('결제요청', $insert_id, null, $app);

                return Response::create('success', Response::HTTP_OK);
            } else {
                return Response::create('fail', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            return Response::create($e->getMessage(), Response::HTTP_OK);
        }
    }

    public function edit(Request $request, Application $app)
    {
        try {
            $paymentid = $request->get('paymentid');
            $key = $request->get('key');
            $value = $request->get('value');

            $payment_service = new UserPaymentService(UserSession::getSelfDto());
            $row = $payment_service->getRowService($paymentid);

            if ($key == 'is_manager_accepted') {
                $result = $row->acceptManager();
            } elseif ($key == 'is_co_accepted') {
                $result = $row->acceptCO();
            } else {
                $result = $row->edit($key, $value);
                if ($key == 'status' && $result == '결제 완료') {
                    UserPaymentMailService::sendMail('결제완료', $paymentid, null, $app);
                }
            }

            if ($result === 1) {
                return Response::create('success', Response::HTTP_OK);
            } else {
                if ($key == 'is_manager_accepted' || $key == 'is_co_accepted' || $result == 'error') {
                    return Response::create($result, Response::HTTP_INTERNAL_SERVER_ERROR);
                } else {
                    return Response::create($result, Response::HTTP_OK);
                }
            }
        } catch (\Exception $e) {
            return Response::create($e->getMessage(), Response::HTTP_OK);
        }
    }

    public function del(Request $request, Application $app)
    {
        try {
            $paymentid = $request->get('paymentid');
            $key = $request->get('key');

            $payment_service = new UserPaymentService(UserSession::getSelfDto());
            $row = $payment_service->getRowService($paymentid);
            if ($key == 'is_manager_rejected') {
                if ($row->rejectManager()) {
                    $reason = $request->getContent();
                    UserPaymentMailService::sendMail('결제반려', $paymentid, $reason, $app);

                    return Response::create('success', Response::HTTP_OK);
                } else {
                    return Response::create('fail', Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            } else {
                $result = $row->del();
                if ($result == 1) {
                    return Response::create('success', Response::HTTP_OK);
                } else {
                    return Response::create($result, Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        } catch (\Exception $e) {
            return Response::create($e->getMessage(), Response::HTTP_OK);
        }
    }

    public function getConst(Request $request)
    {
        $key = $request->get('key');

        return JsonResponse::create(UserPaymentConst::getConstValueByKey($key), Response::HTTP_OK);
    }

    private function getCsvResponse($payment_dict)
    {
        $payment_service = new UserPaymentStatService();
        $payments = PaymentDtoFactory::importFromDatabaseDicts($payment_dict);

        return $payment_service->getCsvRespose($payments);
    }

    private function getBankTransferCsvResponse($payment_dict)
    {
        $payment_service = new UserPaymentStatService();
        $payments = PaymentDtoFactory::importFromDatabaseDicts($payment_dict);

        return $payment_service->getBankTransferCsvRespose($payments);
    }

    public function download(Request $request)
    {
        if (!UserPolicy::isPaymentAdmin(UserSession::getSelfDto())) {
            return new Response("권한이 없습니다", Response::HTTP_UNAUTHORIZED);
        }

        $month = $request->get('month');
        $month = UserPaymentRequestFilter::parseMonth($month);
        $month = date('Y/m/1', strtotime($month));
        $user_payment_model = new PaymentModel();

        return $this->getCsvResponse($user_payment_model->getAllPayments($month));
    }

    public function downloadActiveCategory(Request $request)
    {
        if (!UserPolicy::isPaymentAdmin(UserSession::getSelfDto())) {
            return new Response("권한이 없습니다", Response::HTTP_UNAUTHORIZED);
        }

        $category = $request->get('category_condition');
        $user_payment_model = new PaymentModel();

        return $this->getCsvResponse($user_payment_model->getAllPaymentsByActiveCategory($category));
    }

    public function downloadActiveMonth(Request $request)
    {
        if (!UserPolicy::isPaymentAdmin(UserSession::getSelfDto())) {
            return new Response("권한이 없습니다", Response::HTTP_UNAUTHORIZED);
        }

        $month = $request->get('month');
        $month = UserPaymentRequestFilter::parseMonth($month);
        $month = date('Y/m/1', strtotime($month));
        $user_payment_model = new PaymentModel();

        return $this->getCsvResponse($user_payment_model->getAllPaymentsByActiveMonth($month));
    }

    public function downloadActiveRequestDate(Request $request)
    {
        if (!UserPolicy::isPaymentAdmin(UserSession::getSelfDto())) {
            return new Response("권한이 없습니다", Response::HTTP_UNAUTHORIZED);
        }

        $requestDateStart = $request->get('request_date_start');
        $requestDateEnd = $request->get('request_date_end');
        $requestDateStart = UserPaymentRequestFilter::parseDate($requestDateStart);
        $requestDateEnd = UserPaymentRequestFilter::parseDate($requestDateEnd);

        $user_payment_model = new PaymentModel();

        return $this->getCsvResponse($user_payment_model->getAllPaymentsByActiveRequestDate($requestDateStart, $requestDateEnd));
    }

    public function downloadActiveTeam(Request $request)
    {
        if (!UserPolicy::isPaymentAdmin(UserSession::getSelfDto())) {
            return new Response("권한이 없습니다", Response::HTTP_UNAUTHORIZED);
        }

        $team = $request->get('team');
        $user_payment_model = new PaymentModel();

        return $this->getCsvResponse($user_payment_model->getAllPaymentsByActiveTeam($team));
    }

    public function downloadRemain()
    {
        if (!UserPolicy::isPaymentAdmin(UserSession::getSelfDto())) {
            return new Response("권한이 없습니다", Response::HTTP_FORBIDDEN);
        }

        $user_payment_model = new PaymentModel();

        return $this->getCsvResponse($user_payment_model->queuedPayments());
    }

    public function downloadTaxDate(Request $request)
    {
        if (!UserPolicy::isPaymentAdmin(UserSession::getSelfDto())) {
            return new Response("권한이 없습니다", Response::HTTP_FORBIDDEN);
        }

        $month = $request->get('month');
        $month = UserPaymentRequestFilter::parseMonth($month);
        $month = date('Y/m/1', strtotime($month));
        $user_payment_model = new PaymentModel();

        return $this->getCsvResponse($user_payment_model->getAllPaymentsByTaxDate($month));
    }

    public function downloadTodayBankTransfer(Request $request)
    {
        if (!UserPolicy::isPaymentAdmin(UserSession::getSelfDto())) {
            return new Response("권한이 없습니다", Response::HTTP_FORBIDDEN);
        }

        $user_payment_model = new PaymentModel();
        $type = $request->get('type');
        if ($type === 'all') {
            return $this->getBankTransferCsvResponse($user_payment_model->todayQueued());
        } elseif ($type === 'confirmed') {
            return $this->getBankTransferCsvResponse($user_payment_model->todayConfirmedQueued());
        } elseif ($type === 'unconfirmed') {
            return $this->getBankTransferCsvResponse($user_payment_model->todayUnconfirmedQueued());
        } else {
            return new Response("조건을 선택하세요.");
        }
    }

    public function downloadFile(Request $request)
    {
        $file_id = $request->get('fileid');
        $file_service = new PaymentFileService();
        $file_location = $file_service->getFileLocation($file_id);

        return new RedirectResponse($file_location);
    }

    public function deleteFile(Request $request)
    {
        try {
            $fileid = $request->get('fileid');
            if (!intval($fileid)) {
                return Response::create("invalid fileid", Response::HTTP_BAD_REQUEST);
            }

            $self = UserSession::getSelfDto();

            if (UserPaymentService::deleteFile($self, $fileid)) {
                return Response::create('success', Response::HTTP_OK);
            } else {
                return Response::create('삭제실패했습니다.', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            return Response::create($e->getMessage(), Response::HTTP_OK);
        }
    }

    public function uploadFile(Request $request)
    {
        try {
            $paymentid = $request->get('paymentid');
            if (!intval($paymentid)) {
                return Response::create("invalid fileid", Response::HTTP_BAD_REQUEST);
            }

            $file = $request->files->get('files')[0];
            if (UserPaymentService::addFiles($paymentid, $file)) {
                return JsonResponse::create('success');
            } else {
                return JsonResponse::create('file upload failed', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (\Exception $e) {
            return Response::create($e->getMessage(), Response::HTTP_OK);
        }
    }

    public function getPayDateByStr(Request $request)
    {
        $pay_type_str = $request->get('pay_type_str');
        $paydate = UserPaymentRequestFilter::getPayDateByStr($pay_type_str);
        if ($paydate) {
            return Response::create($paydate, Response::HTTP_OK);
        } else {
            return Response::create('fail', Response::HTTP_OK);
        }
    }
}
