<?php
declare(strict_types=1);

namespace Intra\Service\Util;

class Util
{
    public static function printAlert(string $msg): string
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
