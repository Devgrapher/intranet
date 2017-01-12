<?php

use Intra\Config\Config;

class ConfigDevelop extends Config
{
    public static $upload_dir;

    public static $mysql_host = '';
    public static $mysql_user = '';
    public static $mysql_password = '';
    public static $mysql_db = '';

    public static $is_dev = true;
}

ConfigDevelop::$upload_dir = __DIR__ . '/upload/';
