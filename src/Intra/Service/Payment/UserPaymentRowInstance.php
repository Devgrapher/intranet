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

        if (!$this->validateEditAuth($key, $old_value, $new_value, $payment_dto)) {
            return $old_value;
        }
        $this->user_payment_model->update($this->payment_id, $key, $new_value);

        $updated_payment_dto = PaymentDtoFactory::createFromDatabaseByPk($this->payment_id);
        $updated_value = $updated_payment_dto->$key;

        if ($key == 'status') {
            if ($updated_value == '결제 완료') {
                $type = '결제완료';
                UserPaymentMailService::sendMail($type, $this->payment_id);
            }
        } elseif ($key == 'price') {
            return number_format($updated_value) . ' 원';
        } elseif ($key == 'manager_uid') {
            $user_name = UserJoinService::getNameByUidSafe($updated_value);
            if ($user_name === null) {
                return 'error';
            }
            return $user_name;
        }
        return $updated_value;
    }

    /**
     * @param $key
     * @param $old_value
     * @param $new_value
     * @param $payment_dto PaymentDto
     * @return bool
     * @throws MsgException
     */
    private function validateEditAuth($key, $old_value, $new_value, $payment_dto)
    {
        if ($key == 'date') {
            //날짜를 변경할때 다른 월로는 변경불가
            $month_new = date('Ym', strtotime($new_value));
            $month_old = date('Ym', strtotime($old_value));
            if ($month_new != $month_old) {
                return false;
            }
        }
        if ($key == 'status') {
            if (!$payment_dto->is_co_accepted || !$payment_dto->is_manager_accepted) {
                throw new MsgException("아직 승인되지 않았습니다");
            }
        }
        $is_payment_admin = UserPolicy::isPaymentAdmin(UserSession::getSelfDto());
        $is_editable = $payment_dto->is_editable;
        if (!($is_payment_admin || $is_editable)) {
            return false;
        }
        return true;
    }

    public function del()
    {
        $payment_dto = PaymentDtoFactory::createFromDatabaseByPk($this->payment_id);
        $this->assertDel($payment_dto);
        $res = $this->user_payment_model->del($this->payment_id);
        if ($res) {
            return 1;
        }
        return '삭제가 실패했습니다!';
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
        return $this->accept('co', $self->uid);
    }

    private function accept($user_type, $uid)
    {
        $payment_accept_dto = PaymentAcceptDto::importFromAddRequest($this->payment_id, $uid, $user_type);
        PaymentAcceptModel::insert($payment_accept_dto);
        return 1;
    }

    public function rejectManager()
    {
        $payment_dto = PaymentDtoFactory::createFromDatabaseByPk($this->payment_id);
        $is_payment_admin = UserPolicy::isPaymentAdmin(UserSession::getSelfDto());
        $is_editable = $payment_dto->is_editable;
        if (!($is_payment_admin || $is_editable)) {
            throw new MsgException("반려 권한이 없습니다.");
        }

        return $this->reject('manager', $payment_dto->manager_uid);
    }

    private function reject($user_type, $uid)
    {
        $payment_accept_dto = PaymentAcceptDto::importFromAddRequest($this->payment_id, $uid, $user_type);
        PaymentAcceptModel::delete($payment_accept_dto);
        return 1;
    }
}
