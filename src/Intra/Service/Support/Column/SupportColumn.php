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
    public $viewOnly = false;
    private $isVisiblePreds;
    private $editableUserPreds;

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

    public function addEditableUserPred(callable $predicate)
    {
        $this->editableUserPreds[] = $predicate;
        return $this;
    }

    public function updateEditableForUser(UserDto $login_user)
    {
        foreach ((array)$this->editableUserPreds as $predicate) {
            if ($predicate($login_user)) {
                $this->readonly = false;
                break;
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

    public function isVisibleIf(callable $predicate)
    {
        $this->isVisiblePreds[] = $predicate;
        return $this;
    }

    public function isVisible(UserDto $login_user)
    {
        if (count($this->isVisiblePreds) == 0) {
            return true;
        }
        foreach ($this->isVisiblePreds as $predicate) {
            if ($predicate($login_user)) {
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
