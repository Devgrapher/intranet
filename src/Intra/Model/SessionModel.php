<?php
namespace Intra\Model;

class SessionModel
{
    public function __construct()
    {
        self::init();
    }

    public static function init()
    {
        if (!isset($_SESSION)) {
            $session_lifetime = 60 * 60 * 24 * 14; //14일
            ini_set("session.gc_maxlifetime", $session_lifetime);
            session_set_cookie_params($session_lifetime, '/', $_SERVER['SERVER_NAME']);
            session_start();
            // http://php.net/session_set_cookie_params
            // As PHP's Session Control does not handle session lifetimes correctly when using session_set_cookie_params(),
            setcookie(session_name(), session_id(), time() + $session_lifetime, '/');
        }
    }

    public function get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    public function set($key, $value)
    {
        return $_SESSION[$key] = $value;
    }
}
