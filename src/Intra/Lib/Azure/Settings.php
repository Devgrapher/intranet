<?php

namespace Intra\Lib\Azure;

class Settings
{
    public static function getClientId()
    {
        $domain = self::getDomain();
        return $_ENV["azure.$domain.clientId"];
    }

    /**
     * @return string
     */
    private static function getDomain()
    {
        $domain = $_ENV['domain'];
        if ($domain == 'ridi.com') {
            return 'ridi';
        } else {
            return 'studiod';
        }
    }

    public static function getPassword()
    {
        $domain = self::getDomain();
        return $_ENV["azure.$domain.password"];
    }

    public static function getRediectURI()
    {
        $domain = self::getDomain();
        return $_ENV["azure.$domain.redirectURI"];
    }

    public static function getResourceURI()
    {
        $domain = self::getDomain();
        return $_ENV["azure.$domain.resourceURI"];
    }

    public static function getAppTenantDomainName()
    {
        $domain = self::getDomain();
        return $_ENV["azure.$domain.appTenantDomainName"];
    }

    public static function getApiVersion()
    {
        $domain = self::getDomain();
        return $_ENV["azure.$domain.apiVersion"];
    }
}
