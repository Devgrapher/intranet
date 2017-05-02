<?php

namespace Intra\Service\Support\Column;

class SupportColumnByValueCallback extends SupportColumn
{
    public $valueCallback;

    public function __construct($column, callable $valueCallback)
    {
        parent::__construct($column);
        $this->valueCallback = $valueCallback;
        $this->noDbColumn = true;
    }
}
