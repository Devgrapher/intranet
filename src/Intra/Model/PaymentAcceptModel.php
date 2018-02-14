<?php
namespace Intra\Model;

use Intra\Core\BaseModel;
use Intra\Service\Payment\PaymentAcceptDto;

class PaymentAcceptModel extends BaseModel
{
    public static function getsByPaymentids(array $payment_ids)
    {
        return self::getDb()->sqlDicts('select * from payment_accept where ?', sqlWhere(['paymentid' => $payment_ids]));
    }

    public static function get($uid, $user_type)
    {
        return self::getDb()->sqlDicts(
            'select * from payment_accept where ?',
            sqlWhere([
                'uid' => $uid,
                'user_type' => $user_type,
            ])
        );
    }

    public static function insert(PaymentAcceptDto $payment_accept_dto)
    {
        $rows = $payment_accept_dto->exportDatabaseInsert();

        return self::getDb()->sqlInsert('payment_accept', $rows);
    }

    public static function delete(PaymentAcceptDto $payment_accept_dto)
    {
        return self::getDb()->sqlDelete('payment_accept', [
            'paymentid' => $payment_accept_dto->paymentid,
            'uid' => $payment_accept_dto->uid,
            'user_type' => $payment_accept_dto->user_type,
        ]);
    }
}
