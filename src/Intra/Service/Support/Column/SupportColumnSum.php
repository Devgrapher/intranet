<?php

namespace Intra\Service\Support\Column;

class SupportColumnSum extends SupportColumn
{
    public $priceColumn;
    public $countColumn;
    public $discountDict;

    public function __construct($column, $priceColumn, $countColumn, $discountDict)
    {
        parent::__construct($column);
        $this->priceColumn = $priceColumn;
        $this->countColumn = $countColumn;
        $this->discountDict = $discountDict;
    }
}
