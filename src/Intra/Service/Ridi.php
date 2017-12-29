<?php
namespace Intra\Service;

use Raven_Autoloader;
use Raven_Client;
use Raven_ErrorHandler;

class Ridi
{
    /**
     * @var Raven_Client
     */
    private static $raven_client;

    public static function isRidiIP($client_ip)
    {
        return preg_match($_ENV['INTRA_IPS'], $client_ip);
    }

    public static function enableSentry()
    {
        $sentry_key = strval($_ENV['SENTRY_KEY']);
        if (strlen($sentry_key) <= 0) {
            return;
        }
        Raven_Autoloader::register();
        self::$raven_client = new Raven_Client($sentry_key);
        $error_handler = new Raven_ErrorHandler(self::$raven_client);
        $error_handler->registerExceptionHandler();
        $error_handler->registerErrorHandler(true, E_ALL & ~E_NOTICE & ~E_STRICT);
        $error_handler->registerShutdownFunction();
    }

    public static function triggerSentryException(\Exception $e)
    {
        if (self::$raven_client instanceof Raven_Client) {
            self::$raven_client->captureException($e);

            return true;
        }

        return false;
    }
}
