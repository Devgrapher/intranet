<?php
/** @var $this Intra\Core\Route */
$this->matchIf('del/{id}')
    ->assertAsInt('id')
    ->query('del');
