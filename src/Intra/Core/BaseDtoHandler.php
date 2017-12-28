<?php
namespace Intra\Core;

class BaseDtoHandler
{
    protected $dto;

    protected function __construct($dto)
    {
        $this->dto = $dto;
    }

    public static function importFromDto($dto)
    {
        $called_class = get_called_class();

        return new $called_class($dto);
    }

    public function exportDto()
    {
        return clone $this->dto;
    }
}
