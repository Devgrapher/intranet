<?php
/** @var $this Intra\Core\Route */
$this->matchIf('/uid/{uid}')
    ->assertAsInt('uid')
    ->isMethod('get')
    ->query('index');

$this->matchIf('/uid/{uid}/year/{year}')
    ->assertAsInt('uid')
    ->assertAsInt('year')
    ->isMethod('get')
    ->query('index');

$this->matchIf('uid/{uid}')
    ->assertAsInt('uid')
    ->isMethod('post')
    ->query('add');

$this->matchIf('/uid/{uid}')
    ->assertAsInt('uid')
    ->isMethod('put')
    ->query('edit');

$this->matchIf('uid/{uid}/{holidayid}')
    ->assertAsInt('uid')
    ->assertAsInt('holidayid')
    ->isMethod('delete')
    ->query('del');

$this->matchIf('/download/{year}')
    ->assertAsInt('year')
    ->query('download');

$this->matchIf('/downloadRemain/{year}')
    ->assertAsInt('year')
    ->query('downloadRemain');
