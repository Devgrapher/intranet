<?php
/** @var $this Intra\Core\Route */
$this->matchIf('/add/{key}/{value}')
    ->assertInArray('key', ['program', 'font'])
    ->query('add');
