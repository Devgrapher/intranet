<?php
/** @var $this Intra\Core\Control */
use Intra\Service\Weekly\Weekly;

$weekly = new Weekly();

try {
    $weekly->assertPermission();
    return ['html' => $weekly->getContents()];
} catch (Exception $e) {
    die($e->getMessage());
}
