<?php
/** @var $this Intra\Core\Control */
use Intra\Model\LightFileModel;

if ($_FILES && $_FILES["fileToUpload"]) {
    $infile = $_FILES["fileToUpload"]["tmp_name"];

    if ($infile) {
        $filebag = new LightFileModel('organization');
        $outfile = $filebag->getLocation(date("Y-m-d") . ".pdf");

        if (!move_uploaded_file($infile, $outfile)) {
            die('파일을 업로드하지 못했습니다.');
        }

        $recent = $filebag->getLocation('recent');

        unlink($recent);
        symlink($outfile, $recent);
    }
}

return [];
