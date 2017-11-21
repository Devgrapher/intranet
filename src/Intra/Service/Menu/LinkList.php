<?php

namespace Intra\Service\Menu;

class LinkList implements LinkInterface
{
    public $is_visible;
    public $title;
    public $glyphicon;
    public $link_list;

    public function __construct(string $title, array $link_list, string $glyphicon = null)
    {
        $this->title = $title;
        $this->link_list = $link_list;
        $this->glyphicon = $glyphicon;

        $this->is_visible = false;
        foreach ($link_list as $link) {
            /* @var Link $link */
            $this->is_visible |= $link->is_visible;
        }
    }

    public function getHtml(): string
    {
        if (!$this->is_visible) {
            return '';
        }

        $html = '<li class="dropdown">';
        $html .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown" ';
        $html .= 'role="button" aria-haspopup="true" aria-expanded="false">';
        if (!empty($this->glyphicon)) {
            $html .= '<span class="glyphicon glyphicon-' . $this->glyphicon . '"></span> ';
        }
        $html .= $this->title;
        $html .= '<span class="caret"></span></a>';
        $html .= '<ul class="dropdown-menu">';
        foreach ($this->link_list as $link) {
            /* @var LinkInterface $link */
            $html .= $link->getHtml();
        }
        $html .= '</ul></li>';

        return $html;
    }
}
