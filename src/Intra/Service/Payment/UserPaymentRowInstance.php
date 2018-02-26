<?php

namespace Intra\Service\Payment;

use Intra\Core\MsgException;
use Intra\Model\PaymentAcceptModel;
use Intra\Model\PaymentModel;
use Intra\Service\User\UserJoinService;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;

class UserPaymentRowInstance
{
    private $user_payment_model;
    private $payment_id;

    public function __construct($payment_id)
    {
        $this->user_payment_model = new PaymentModel();
        $this->payment_id = $payment_id;
    }

    public function edit($key, $new_value)
    {
        $payment_dto = PaymentDtoFactory::createFromDatabaseByPk($this->payment_id);
        $old_value = $payment_dto->$key;

        $this->validateEditAuth($key, $old_value, $new_value, $payment_dto);
        $this->user_payment_model->update($this->payment_id, $key, $new_value);

        $updated_payment_dto = PaymentDtoFactory::createFromDatabaseByPk($this->payment_id);
        $updated_value = $updated_payment_dto->$key;

        switch ($key) {
            case 'price':
                return str_replace('.00', '', number_format($updated_value, 2));
            case 'manager_uid':
                $payment_accept_dicts = PaymentAcceptModel::get(
                    $this->payment_id,
                    $payment_dto->manager_uid,
                    'manager'
                );
                foreach ($payment_accept_dicts as $payment_accept_dict) {
                    $payment_accept_dto = PaymentAcceptDto::importFromDatabaseDict($payment_accept_dict);
                    PaymentAcceptModel::delete($payment_accept_dto);
                }
                return UserJoinService::getNameByUidSafe($updated_value);
            default:
                return $updated_value;
        }
    }

    /**
     * @param $key
     * @param $old_value
     * @param $new_value
     * @param $payment_dto PaymentDto
     * @throws MsgException
     */
    private function validateEditAuth($key, $old_value, $new_value, $payment_dto)
    {
        $is_payment_admin = UserPolicy::isPaymentAdmin(UserSession::getSelfDto());
        $is_editable = $payment_dto->is_editable;
        if (!($is_payment_admin || $is_editable)) {
            throw new MsgException('변경 권한이 없습니다');
        }
        switch ($key) {
            case 'date':
                //날짜를 변경할때 다른 월로는 변경불가
                $month_new = date('Ym', strtotime($new_value));
                $month_old = date('Ym', strtotime($old_value));
                if ($month_new != $month_old) {
                    throw new MsgException('같은 달의 날짜로만 변경가능합니다');
                }
                break;
            case 'status':
                if (!$payment_dto->is_co_accepted || !$payment_dto->is_manager_accepted) {
                    throw new MsgException('아직 승인되지 않았습니다');
                }
                break;
        }
    }

    public function del()
    {
        $payment_dto = PaymentDtoFactory::createFromDatabaseByPk($this->payment_id);
        $this->assertDel($payment_dto);
        if (!$this->user_payment_model->del($this->payment_id)) {
            throw new MsgException('삭제가 실패했습니다!');
        }
    }

    private function assertDel($payment_dto)
    {
        $is_payment_admin = UserPolicy::isPaymentAdmin(UserSession::getSelfDto());
        $is_editable = $payment_dto->is_editable;
        if (!($is_payment_admin || $is_editable)) {
            throw new MsgException("삭제 권한이 없습니다.");
        }

        return true;
    }

    public function acceptManager()
    {
        $payment_dto = PaymentDtoFactory::createFromDatabaseByPk($this->payment_id);
        $self = UserSession::getSelfDto();
        if ($payment_dto->manager_uid != $self->uid) {
            throw new MsgException("담당 승인자가 아닙니다.");
        }

        return $this->accept('manager', $self->uid);
    }

    public function acceptCO()
    {
        $self = UserSession::getSelfDto();
        if (!UserPolicy::isPaymentAdmin($self)) {
            throw new MsgException("담당 승인자가 아닙니다.");
        }

        $payment_dto = PaymentDtoFactory::createFromDatabaseByPk($this->payment_id);
        if (!$payment_dto->is_manager_accepted) {
            throw new MsgException('승인자 확인이 필요합니다');
        }

        return $this->accept('co', $self->uid);
    }

    private function accept($user_type, $uid)
    {
        $old = PaymentAcceptModel::get($this->payment_id, $uid, $user_type);
        if (count($old)) {
            throw new MsgException('이미 승인되었습니다.');
        }
        $payment_accept_dto = PaymentAcceptDto::importFromAddRequest($this->payment_id, $uid, $user_type);
        PaymentAcceptModel::insert($payment_accept_dto);
    }

    public function rejectManager()
    {
        $payment_dto = PaymentDtoFactory::createFromDatabaseByPk($this->payment_id);
        $is_payment_admin = UserPolicy::isPaymentAdmin(UserSession::getSelfDto());
        $is_editable = $payment_dto->is_editable;
        if (!($is_payment_admin || $is_editable)) {
            throw new MsgException("반려 권한이 없습니다.");
        }
        $this->reject('manager', $payment_dto->manager_uid);
    }

    private function reject($user_type, $uid)
    {
        $payment_accept_dto = PaymentAcceptDto::importFromAddRequest($this->payment_id, $uid, $user_type);
        PaymentAcceptModel::delete($payment_accept_dto);
    }
}
