<?php

namespace Intra\Service\Support\Column;

class SupportColumnDate extends SupportColumn
{
    /**
     * @var string
     */
    private $string;

    /**
     * SupportColumnDate constructor.
     *
     * @param string $string
     * @param string $default
     */
    public function __construct($string, $default = '')
    {
        parent::__construct($string);
        $this->default = $default;
        $this->string = $string;
    }
}
