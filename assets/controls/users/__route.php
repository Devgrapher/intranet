<?php
/** @var $this Intra\Core\Route */
$this->matchIf('/{userid}/updateExtra/{key}/{value}')
    ->query('updateExtra.ajax');

$this->matchIf('jeditable_key/{key}')
    ->query('jeditable_key');

$this->matchIf('/join')
    ->isMethod('post')
    ->query('join.ajax');

$this->matchIf('/{uid}/image')
    ->query('/image');

$this->matchIf('/{uid}/thumb')
    ->query('/thumb');
