<?php
/** @var $this Intra\Core\Route */
$this->matchIf('uid/{uid}')
    ->assertAsInt('uid')
    ->isMethod('get')
    ->query('index');

$this->matchIf('uid/{uid}/month/{month}')
    ->assertAsInt('uid')
    ->isMethod('get')
    ->query('index');

$this->matchIf('uid/{uid}')
    ->assertAsInt('uid')
    ->isMethod('post')
    ->query('add');

$this->matchIf('receiptid/{receiptid}')
    ->assertAsInt('receiptid')
    ->isMethod('put')
    ->query('edit');

$this->matchIf('receiptid/{receiptid}')
    ->assertAsInt('receiptid')
    ->isMethod('delete')
    ->query('del');

$this->matchIf('download/{month}')
    ->query('download');

$this->matchIf('downloadYear/{month}')
    ->query('downloadYear');
