<?php

namespace Intra\Service\Menu;

use Intra\Service\Auth\ExceptOuter;
use Intra\Service\Auth\Superclass\AuthMultiplexer;
use Intra\Service\User\UserSession;

class Link implements LinkInterface
{
    public $is_visible;
    public $title;
    public $url;
    public $target;
    public $glyphicon;
    public $label;
    public $label_type;

    /**
     * Link constructor.
     *
     * @param                      $title
     * @param                      $url
     * @param AuthMultiplexer      $auth_checker
     * @param null                 $target
     * @param null                 $glyphicon
     * @param string               $label
     * @param string               $label_type
     */
    public function __construct($title, $url, $auth_checker = null, $target = null, $glyphicon = null, $label = null, $label_type = 'default')
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
        $this->label = $label;
        $this->label_type = $label_type;
    }

    public function getHtml(): string
    {
        if (!$this->is_visible) {
            return '';
        }

        $html = '<li><a href="' . $this->url . '" target="' . $this->target . '">';
        if (!empty($this->glyphicon)) {
            $html .= '<span class="glyphicon glyphicon-' . $this->glyphicon . '"></span> ';
        }
        $html .= $this->title;
        if (!empty($this->label)) {
            $html .= ' <span class="label label-' . $this->label_type . '">' . $this->label . '</span>';
        }
        $html .= '</a></li>';

        return $html;
    }
}
