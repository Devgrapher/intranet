<?php
/** @var $this Intra\Core\Control */
use Intra\Model\LightFileModel;
use Intra\Service\Weekly\Weekly;

$infile = $_FILES["fileToUpload"]["tmp_name"];
$filebag = new LightFileModel('weekly');
$filename = Weekly::getFilename();
$outfile = $filebag->getLocation($filename);

if ($infile) {
    Weekly::dumpToHtml($infile, $outfile);
}

return [];
