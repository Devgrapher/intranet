<?php
declare(strict_types=1);

namespace Intra\Service\Support;

use Intra\Model\DinnerOrderModel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class SupportDinnerService
{
    const TIME_DINNER_ORDER_START = '15:00';
    const TIME_DINNER_ORDER_END = '17:30';
    const MSG_DINNER_ORDER_NOT_EXISTS = '가능한 저녁주문이 없습니다. 관리자에게 문의해주세요.';
    const MSG_DINNER_ORDER_NOT_AVAILABLE = '저녁주문은 평일 오후 3시부터 5시40분까지 신청 가능합니다.';

    public static function getResponse(): Response
    {
        if (!self::isDinnerOrderTime()) {
            return new Response(self::printAlert(self::MSG_DINNER_ORDER_NOT_AVAILABLE));
        }

        $dinner_order = DinnerOrderModel::find(date('w'));
        if (!$dinner_order) {
            return new Response(self::printAlert(self::MSG_DINNER_ORDER_NOT_EXISTS));
        }

        return new RedirectResponse($dinner_order['order_url']);
    }

    private static function isDinnerOrderTime(): bool
    {
        $now = new \DateTime();
        $dinner_start = new \DateTime(self::TIME_DINNER_ORDER_START);
        $dinner_end = new \DateTime(self::TIME_DINNER_ORDER_END);
        return $dinner_start <= $now && $now <= $dinner_end;
    }

    private static function printAlert(string $msg): string
    {
        $html = '<!doctype html><html><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1"></head><body><script>';
        if (!empty($msg)) {
            $html .= "alert(" . json_encode($msg) . ");";
        }
        $html .= "close();";
        $html .= "</script></body></html>\n";

        return $html;
    }
}
