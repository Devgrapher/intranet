<?php
/** @var $this Intra\Core\Control */
use Intra\Config\Config;
use Intra\Lib\Azure\AuthorizationHelperForAADGraphService;

$azure_login = AuthorizationHelperForAADGraphService::getAuthorizatonURL();

if (Config::$is_dev) {
    $azure_login = '/';
}

return compact('azure_login');
