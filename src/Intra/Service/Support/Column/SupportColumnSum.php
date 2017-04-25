<?php

namespace Intra\Service\Support\Column;

class SupportColumnSum extends SupportColumn
{
    public $operands;

    /**
     * initSupportColumnMutual constructor.
     *
     * @param $column
     * @param $operands
     */
    public function __construct($column, $operands)
    {
        parent::__construct($column);
        $this->operands = $operands;
    }
}
