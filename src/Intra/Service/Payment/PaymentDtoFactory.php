<?php
namespace Intra\Service\Payment;

use Intra\Model\PaymentAcceptModel;
use Intra\Model\PaymentModel;
use Ridibooks\Platform\Common\DictsUtils;

class PaymentDtoFactory
{
    /**
     * @param $payment_id
     * @return PaymentDto
     */
    public static function createFromDatabaseByPk($payment_id)
    {
        $user_payment_model = new PaymentModel();
        $payment_row = $user_payment_model->getPaymentWithoutUid($payment_id);
        if (!$payment_row) {
            return null;
        }
        return self::importFromDatabaseDicts([$payment_row])[0];
    }

    /**
     * @param array $payment_dicts
     * @return PaymentDto[]
     */
    public static function importFromDatabaseDicts(array $payment_dicts)
    {
        if (count($payment_dicts) == 0) {
            return [];
        }
        $paymentids = DictsUtils::extractValuesByKey($payment_dicts, 'paymentid');

        $payment_accept_dicts = PaymentAcceptModel::getsByPaymentids($paymentids);
        $payment_accept_dicts_by_payment_id = DictsUtils::alignListByKey($payment_accept_dicts, 'paymentid');

        $payment_files_dicts = FileUploadModel::getDictsByGroupAndKeys('payment_files', $paymentids);
        $payment_files_dicts_by_payment_id = DictsUtils::alignListByKey($payment_files_dicts, 'key');

        $return = [];
        foreach ($payment_dicts as $payment_dict) {
            $paymentid = $payment_dict['paymentid'];

            $payment_accepts_dicts = $payment_accept_dicts_by_payment_id[$paymentid];
            $payment_accept_dtos = PaymentAcceptDtoFactory::createFromDatabaseDicts($payment_accepts_dicts);

            $payment_files_dicts = $payment_files_dicts_by_payment_id[$paymentid];
            $payment_files_dtos = FileUploadDtoFactory::createFromDatabaseDicts($payment_files_dicts);

            $return[] = PaymentDto::importFromDatabase($payment_dict, $payment_accept_dtos, $payment_files_dtos);
        }
        return $return;
    }
}
