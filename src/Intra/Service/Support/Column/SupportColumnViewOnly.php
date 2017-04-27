<?php

namespace Intra\Service\Support\Column;

class SupportColumnViewOnly extends SupportColumn
{
    public $dataCallback;

    /**
     * initSupportColumnMutual constructor.
     *
     * @param $column
     * @param callable $dataCallback
     */
    public function __construct($column, callable $dataCallback)
    {
        parent::__construct($column);
        $this->dataCallback = $dataCallback;
        $this->viewOnly = true;
    }
}
