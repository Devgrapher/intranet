<?php

namespace Intra\Service\Weekly;

use DateTime;
use Exception;
use Intra\Service\File\WeeklyScheduleFileService;
use Intra\Service\Ridi;
use Intra\Service\User\UserSession;
use PHPExcel_Reader_Excel2007;
use PHPExcel_Writer_HTML;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class Weekly
{
    private static function dumpToHtml($infile, $outfile)
    {
        $reader = new PHPExcel_Reader_Excel2007();
        $excel = $reader->load($infile);

        $writer = new PHPExcel_Writer_HTML($excel);
        $writer->save($outfile);
    }

    public static function upload($uploaded_file)
    {
        $file_path = $uploaded_file->getRealPath();
        self::dumpToHtml($file_path, $file_path);

        $self = UserSession::getSelfDto();
        $now = new DateTime();
        $file_service = new WeeklyScheduleFileService();
        $file_service->uploadFile(
            $self->uid,
            $now->format('W'),
            pathinfo($file_path, PATHINFO_BASENAME),
            file_get_contents($file_path),
            'text/html'
        );
    }

    public static function assertPermission(Request $req)
    {
        if (!Ridi::isRidiIP($req->getClientIp()) || UserSession::isTa()) {
            throw new Exception('권한이 없습니다.');
        }

        // 월~수요일에만 열람 가능
        if (date('w') != 1 && date('w') != 2 && date('w') != 3) {
            throw new Exception('열람 가능한 요일이 아닙니다.');
        }
    }

    public static function getContents()
    {
        $now = new DateTime();
        $file_service = new WeeklyScheduleFileService();
        $file_location = $file_service->getLastFileLocation($now->format('W'));
        if (empty($file_location)) {
            throw new Exception('내용이 준비되지 않았습니다.');
        }

        return new RedirectResponse($file_location);
    }
}
