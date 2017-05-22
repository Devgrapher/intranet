<?php

namespace Intra\Lib\Azure;

class Settings
{
    public static function getClientId()
    {
        return $_ENV["azure.clientId"];
    }

    public static function getPassword()
    {
        return $_ENV["azure.password"];
    }

    public static function getRediectURI()
    {
        return $_ENV["azure.redirectURI"];
    }

    public static function getResourceURI()
    {
        return $_ENV["azure.resourceURI"];
    }

    public static function getAppTenantDomainName()
    {
        return $_ENV["azure.appTenantDomainName"];
    }

    public static function getApiVersion()
    {
        return $_ENV["azure.apiVersion"];
    }
}
