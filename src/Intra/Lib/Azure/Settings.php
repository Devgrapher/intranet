<?php

namespace Intra\Lib\Azure;

class Settings
{
    public static function getClientId()
    {
        return $_ENV["azure_clientId"];
    }

    public static function getPassword()
    {
        return $_ENV["azure_password"];
    }

    public static function getRediectURI()
    {
        return $_ENV["azure_redirectURI"];
    }

    public static function getResourceURI()
    {
        return $_ENV["azure_resourceURI"];
    }

    public static function getAppTenantDomainName()
    {
        return $_ENV["azure_appTenantDomainName"];
    }

    public static function getApiVersion()
    {
        return $_ENV["azure_apiVersion"];
    }
}
