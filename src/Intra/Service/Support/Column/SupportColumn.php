<?php

namespace Intra\Service\Support\Column;

use Intra\Service\User\UserDto;

class SupportColumn
{
    public $key;
    public $class_name;

    public $readonly = false;
    public $required = false;
    public $textInputType = 'text';
    public $placeholder = '';
    public $default = '';
    private $isVisibleCallbacks;
    private $editableCallbacks;

    public function __construct($column_name)
    {
        $this->key = $column_name;
        $class_name = preg_replace('/\w+\\\\/', '', get_called_class());
        $this->class_name = $class_name;
    }

    public function readonly()
    {
        $this->readonly = true;
        return $this;
    }

    public function editableOnlyIf(callable $callback)
    {
        $this->editableCallbacks[] = $callback;
        return $this;
    }

    public function updateReadonly(UserDto $user_dto)
    {
        if (count($this->editableCallbacks) > 0) {
            foreach ($this->editableCallbacks as $callback) {
                $this->readonly = !$callback($user_dto);
            }
        }
    }

    public function placeholder($placeholder)
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function defaultValue($default)
    {
        $this->default = $default;
        return $this;
    }

    public function isVisibleIf(callable $callback)
    {
        $this->isVisibleCallbacks[] = $callback;
        return $this;
    }

    public function isVisible(UserDto $user_dto)
    {
        if (count($this->isVisibleCallbacks) == 0) {
            return true;
        }
        foreach ($this->isVisibleCallbacks as $callback) {
            if ($callback($user_dto)) {
                return true;
            }
        }
        return false;
    }

    public function isRequired()
    {
        $this->required = true;
        return $this;
    }

    public function setTextInputType($type)
    {
        $this->textInputType = $type;
        return $this;
    }
}
