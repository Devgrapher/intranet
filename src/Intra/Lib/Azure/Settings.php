<?php

namespace Intra\Lib\Azure;

class Settings
{
    public static function getClientId()
    {
        return $_ENV["AZURE_CLIENTID"];
    }

    public static function getPassword()
    {
        return $_ENV["AZURE_PASSWORD"];
    }

    public static function getRediectURI()
    {
        return $_ENV["AZURE_REDIRECT_URI"];
    }

    public static function getResourceURI()
    {
        return $_ENV["AZURE_RESOURCE_URI"];
    }

    public static function getAppTenantDomainName()
    {
        return $_ENV["AZURE_APPTENANT_DOMAINNAME"];
    }

    public static function getApiVersion()
    {
        return $_ENV["AZURE_API_VERSION"];
    }
}
