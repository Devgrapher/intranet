<?php

namespace Intra\Service\Menu;

use Intra\Service\Auth\ExceptOuter;
use Intra\Service\Auth\Superclass\AuthMultiplexer;
use Intra\Service\User\UserSession;

class Link
{
    public $is_visible;
    public $title;
    public $url;
    public $target;
    public $glyphicon;

    /**
     * Link constructor.
     *
     * @param                      $title
     * @param                      $url
     * @param AuthMultiplexer      $auth_checker
     * @param null                 $target
     * @param null                 $glyphicon
     */
    public function __construct($title, $url, $auth_checker = null, $target = null, $glyphicon = null)
    {
        /*
         * @var AuthMultiplexer
         */
        if (is_null($auth_checker)) {
            $auth_checker = new ExceptOuter();
        }

        $this->title = $title;
        $this->url = $url;
        $this->is_visible = $auth_checker->multiplexingAuth(UserSession::getSelfDto());
        $this->target = $target;
        $this->glyphicon = $glyphicon;
    }
}
