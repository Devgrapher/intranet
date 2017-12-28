<?php
namespace Intra\Service\Payment;

class PaymentAcceptDtoFactory
{
    public static function createFromDatabaseDicts($payment_accepts_dicts)
    {
        $return = [];
        if (is_array($payment_accepts_dicts)) {
            foreach ($payment_accepts_dicts as $payment_accepts_dict) {
                $return[] = PaymentAcceptDto::importFromDatabaseDict($payment_accepts_dict);
            }
        }

        return $return;
    }
}
