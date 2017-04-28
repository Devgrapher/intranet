<?php

namespace Intra\Service\Support\Column;

class SupportColumnCategory extends SupportColumn
{
    /**
     * @var array
     */
    public $category_items;

    /**
     * SupportColumnCategory constructor.
     *
     * @param string $string
     * @param array  $category_items
     * @param array  $category_values
     */
    public function __construct($string, $category_items, $category_values = null)
    {
        parent::__construct($string);
        $this->category_items = array_combine($category_items, isset($category_values) ? $category_values : $category_items);
    }
}
