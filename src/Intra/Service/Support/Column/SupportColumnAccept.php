<?php
namespace Intra\Service\Support\Column;

class SupportColumnAccept extends SupportColumn
{
    private $callback_accept_filter;

    public function __construct($string, callable $callback_accept_filter = null)
    {
        parent::__construct($string);
        $this->callback_accept_filter = $callback_accept_filter;
    }

    public function isAcceptReady($row_dict)
    {
        if ($this->callback_accept_filter) {
            return ($this->callback_accept_filter)($row_dict);
        }
        return true;
    }
}
