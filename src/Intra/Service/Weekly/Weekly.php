<?php

namespace Intra\Service\Weekly;

use Exception;
use Intra\Model\LightFileModel;
use Intra\Service\Ridi;
use Intra\Service\User\UserSession;
use PHPExcel_Reader_Excel2007;
use PHPExcel_Writer_HTML;

class Weekly
{
    public static function dumpToHtml($infile, $outfile)
    {
        $reader = new PHPExcel_Reader_Excel2007();
        $excel = $reader->load($infile);

        $writer = new PHPExcel_Writer_HTML($excel);
        $writer->save($outfile);
    }

    public function assertPermission()
    {
        if (!Ridi::isRidiIP() || UserSession::isTa()) {
            throw new Exception('권한이 없습니다.');
        }

        // 월~수요일에만 열람 가능
        if (date('w') != 1 && date('w') != 2 && date('w') != 3) {
            throw new Exception('열람 가능한 요일이 아닙니다.');
        }
    }

    public function getContents()
    {
        $filebag = new LightFileModel('weekly');

        $filename = self::getFilename();
        if (!$filebag->isExist($filename)) {
            // 지난주 데이터라도
            $fallback_date = mktime(0, 0, 0, date("m"), date("d") - 7, date("Y"));
            $filename = self::getFilename($fallback_date);
            if (!$filebag->isExist($filename)) {
                throw new Exception('내용이 준비되지 않았습니다.');
            }
        }
        $html = file_get_contents($filebag->getLocation($filename));
        $html = str_replace(
            '#FF0000',
            '#888888',
            $html
        );
        return $html;
    }

    public static function getFilename($timestamp = null)
    {
        return date("Ym", $timestamp) . '-' . floor((date('d', $timestamp) - 1) / 7 + 1) . ".html";
    }
}
