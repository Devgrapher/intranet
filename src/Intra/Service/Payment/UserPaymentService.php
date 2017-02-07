<?php
namespace Intra\Service\Payment;

use Intra\Core\MsgException;
use Intra\Model\PaymentModel;
use Intra\Service\User\UserConstant;
use Intra\Service\User\UserDto;
use Intra\Service\User\UserDtoFactory;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserPaymentService
{
    private $payment_model;
    /**
     * @var UserDto
     */
    private $user;

    public function __construct(UserDto $user)
    {
        $this->user = $user;

        $this->payment_model = new PaymentModel();
    }

    /**
     * @param $payment_id
     * @param $file UploadedFile
     *
     * @return UploadedFile|false
     * @throws MsgException
     */
    public static function addFiles($payment_id, $file)
    {
        $self = UserSession::getSelfDto();
        $payment = PaymentDtoFactory::createFromDatabaseByPk($payment_id);
        self::assertAddFiles($payment, $self);

        $file_upload_service = new FileUploadService('payment_files');
        return $file_upload_service->upload($self->uid, $payment_id, $file);
    }

    /**
     * @param $payment PaymentDto
     * @param $self    UserDto
     *
     * @throws MsgException
     */
    private static function assertAddFiles($payment, $self)
    {
        if (UserPolicy::isPaymentAdmin($self)) {
            return;
        }
        if ($self->uid != $payment->uid
            && $self->uid != $payment->manager_uid
        ) {
            throw new MsgException("본인이나 승인자만 파일을 업로드 가능합니다.");
        }
    }

    public static function downloadFile($self, $fileid)
    {
        $file_upload_dto = FileUploadDtoFactory::importDtoByPk($fileid);
        $payment_dto = PaymentDtoFactory::createFromDatabaseByPk($file_upload_dto->key);
        self::assertAccessFile($self, $file_upload_dto, $payment_dto);

        $file_upload_service = new FileUploadService('payment_files');
        return $file_upload_service->getBinaryFileResponseWithDto($file_upload_dto);
    }

    /**
     * @param $self            UserDto
     * @param $file_upload_dto FileUploadDto
     * @param $payment_dto     PaymentDto
     *
     * @throws MsgException
     */
    private static function assertAccessFile($self, $file_upload_dto, $payment_dto)
    {
        if (UserPolicy::isPaymentAdmin($self)) {
            return;
        }
        if ($self->uid == $file_upload_dto->uid) {
            return;
        }
        if (!$payment_dto) {
            throw new MsgException("파일에 해당하는 결제정보가 없습니다. 플랫폼팀에 문의해주세요.");
        }
        if ($self->uid == $payment_dto->manager_uid) {
            return;
        }
        throw new MsgException("파일 다운로드 권한이 없습니다.");
    }

    public static function deleteFile($self, $fileid)
    {
        $file_upload_dto = FileUploadDtoFactory::importDtoByPk($fileid);
        $payment_dto = PaymentDtoFactory::createFromDatabaseByPk($file_upload_dto->key);
        self::assertAccessFile($self, $file_upload_dto, $payment_dto);
        self::assertDeleteFile($self, $file_upload_dto, $payment_dto);

        $file_upload_service = new FileUploadService('payment_files');
        return $file_upload_service->remove($file_upload_dto);
    }

    private static function assertDeleteFile($self, $file_upload_dto, $payment_dto)
    {
        if (UserPolicy::isPaymentAdmin($self)) {
            return;
        }
        if ($payment_dto->is_co_accepted || $payment_dto->is_manager_accepted) {
            throw new MsgException("승인된 이후에는 재무팀만 변경할 수 있습니다. 파일을 재무팀에 전달해주세요.");
        }
    }

    public function index($month, $type)
    {
        $return = [];
        $return['user'] = $this->user;
        $uid = $this->user->uid;

        $prevmonth = date('Y-m', strtotime('-1 month', strtotime($month)));
        $nextmonth = date('Y-m', strtotime('+1 month', strtotime($month)));

        $return['month'] = $month;
        $return['prevmonth'] = $prevmonth;
        $return['nextmonth'] = $nextmonth;
        $return['todayMonth'] = date('Y-m');
        $return['todayDate'] = date('Y-m-d');

        $self = UserSession::getSelfDto();
        $queued_payment_dicts = $this->payment_model->queuedPayments();
        if (UserPolicy::isPaymentAdmin($self)) {
            $return['queuedPayments'] = PaymentDtoFactory::importFromDatabaseDicts($queued_payment_dicts);
            $return['todayQueuedCount'] = $this->payment_model->todayQueuedCount();
            $return['todayQueuedCost'] = $this->payment_model->todayQueuedCost();
        }
        $return['currentUid'] = $this->user->uid;
        $return['selfUid'] = $self->uid;

        if ($type == 'remain') {
            if (UserPolicy::isPaymentAdmin($self)) {
                $payment_dicts = $queued_payment_dicts;
            } else {
                $payment_dicts = $this->payment_model->queuedPaymentsByManager($this->user->uid);
            }
        } elseif ($type == 'today') {
            if (UserPolicy::isPaymentAdmin($self)) {
                $payment_dicts = $this->payment_model->todayQueued();
            } else {
                $payment_dicts = [];
            }
        } else {
            $payment_dicts = $this->payment_model->getPayments($uid, $month);
            if ($self->team == UserConstant::TEAM_HUMAN_MANAGE && !UserPolicy::isTa($self)) {
                $payment_dicts_append = $this->payment_model->getPaymentsWithOption($month, ['category' => [UserPaymentConst::CATEGORY_ASSETS, UserPaymentConst::CATEGORY_WELFARE_EXPENSE]]);
                $payment_dicts = array_merge($payment_dicts, $payment_dicts_append);
                $payment_dicts = array_unique($payment_dicts, SORT_REGULAR);
            }
            if ($self->team == UserConstant::TEAM_CCPQ) {
                $payment_dicts_append = $this->payment_model->getPaymentsWithOption($month, ['category' => [UserPaymentConst::CATEGORY_USER_BOOK_CANCELMENT]]);
                $payment_dicts = array_merge($payment_dicts, $payment_dicts_append);
                $payment_dicts = array_unique($payment_dicts, SORT_REGULAR);
            }
            if ($self->team == UserConstant::TEAM_DEVICE) {
                $payment_dicts_append = $this->payment_model->getPaymentsWithOption($month, ['category' => [UserPaymentConst::CATEGORY_USER_DEVICE_CANCELMENT]]);
                $payment_dicts = array_merge($payment_dicts, $payment_dicts_append);
                $payment_dicts = array_unique($payment_dicts, SORT_REGULAR);
            }
        }
        $payments = PaymentDtoFactory::importFromDatabaseDicts($payment_dicts);
        $return['payments'] = $payments;

        if (UserPolicy::isPaymentAdmin($self)) {
            $return['isSuperAdmin'] = 1;
            $return['editable'] |= 1;
        }

        $return['allCurrentUsers'] = UserDtoFactory::createAvailableUserDtos();
        $return['managerUsers'] = UserDtoFactory::createManagerUserDtos();
        $return['allUsers'] = UserDtoFactory::createAllUserDtos();

        $return['const'] = UserPaymentConst::get();

        if ($type == 'remain') {
            if (UserPolicy::isPaymentAdmin($self)) {
                $return['title'] = '모든 미결제 항목(관리자)';
            } else {
                $return['title'] = '모든 미승인 목록';
            }
        } elseif ($type == 'today') {
            $return['title'] = '오늘 결제 예정';
        }

        return $return;
    }

    /**
     * @param PaymentDto $payment_dto
     *
     * @return int
     * @throws \Exception
     */
    public function add(PaymentDto $payment_dto)
    {
        $insert_id = null;
        PaymentModel::create()->transactional(function ($db) use ($payment_dto, &$insert_id) {
            $payment_model = PaymentModel::create($db);
            $insert_id = $payment_model->add($payment_dto->exportDatabaseInsert());
            if (!$insert_id) {
                throw new \Exception('자료추가 실패했습니다');
            }
            $payment_model->updateUuid($insert_id);
        });

        return $insert_id;
    }

    public function getRowService($paymentid)
    {
        if (UserPolicy::isPaymentAdmin($this->user)) {
            $payment = $this->payment_model->getPaymentWithoutUid($paymentid);
        } else {
            $payment = $this->payment_model->getPayment($paymentid, $this->user->uid);
        }
        if (!$payment) {
            throw new \Exception('invalid paymentid request');
        }
        $paymentid = $payment['paymentid'];
        return new UserPaymentRowInstance($paymentid);
    }
}
