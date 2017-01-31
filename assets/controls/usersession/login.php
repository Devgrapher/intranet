<?php
/** @var $this Intra\Core\Control */
use Intra\Lib\Azure\AuthorizationHelperForAADGraphService;

$azure_login = AuthorizationHelperForAADGraphService::getAuthorizatonURL();

if ($_ENV['is_dev']) {
    $azure_login = '/';
}

return compact('azure_login');
