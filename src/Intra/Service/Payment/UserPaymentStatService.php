<?php
namespace Intra\Service\Payment;

use Intra\Service\User\UserJoinService;
use Intra\Service\User\UserPolicy;
use Intra\Service\User\UserSession;
use Ridibooks\Platform\Common\CsvResponse;
use Symfony\Component\HttpFoundation\Response;

class UserPaymentStatService
{
    /**
     * @param $payments PaymentDto[]
     * @return string
     */
    public function getCsvRespose($payments)
    {
        if (!UserPolicy::isPaymentAdmin(UserSession::getSelfDto())) {
            return new Response("권한이 없습니다", 403);
        }
        //header
        $csvs = [];
        $arr = [
            'uuid',
            '요청일',
            '요청자',
            '승인자',
            '장부반영여부',
            '귀속월',
            '귀속부서',
            '부서세부분류',
            '프로덕트',
            '분류',
            '상세내역',
            '업체명',
            '입금금액',
            '결제예정일',
            '세금계산서수취여부',
            '세금계산서일자',
            '입금은행',
            '입금계좌번호',
            '예금주',
            '비고',
            '결제수단',
            '상태'
        ];
        $csvs[] = $arr;
        foreach ($payments as $payment) {
            $arr = [
                $payment->uuid,
                $payment->request_date,
                UserJoinService::getNameByUidSafe($payment->uid),
                UserJoinService::getNameByUidSafe($payment->manager_uid),
                $payment->is_account_book_registered,
                $payment->month,
                $payment->team,
                $payment->team_detail,
                $payment->product,
                $payment->category,
                $payment->desc,
                $payment->company_name,
                $payment->price,
                $payment->pay_date,
                $payment->tax,
                $payment->tax_date,
                $payment->bank,
                '"' . $payment->bank_account . '"',
                $payment->bank_account_owner,
                $payment->note,
                $payment->paytype,
                $payment->status,
            ];
            $csvs[] = $arr;
        }

        return new CsvResponse($csvs);
    }
}
