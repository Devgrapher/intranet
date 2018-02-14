<?php

namespace Intra\Service\Menu;

class LinkList implements LinkInterface
{
    public $is_visible;
    public $title;
    public $glyphicon;
    public $label;
    public $label_type;
    public $link_list;

    public function __construct(string $title, array $link_list, string $glyphicon = null, string $label = null, string $label_type = 'default')
    {
        $this->title = $title;
        $this->link_list = $link_list;
        $this->glyphicon = $glyphicon;
        $this->label = $label;
        $this->label_type = $label_type;

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
        if (!empty($this->label)) {
            $html .= ' <span class="label label-' . $this->label_type . '">' . $this->label . '</span>';
        }
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
