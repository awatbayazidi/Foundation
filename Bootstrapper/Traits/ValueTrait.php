<?php
namespace AwatBayazidi\Foundation\Bootstrapper\Traits;


use AwatBayazidi\Abzar\Facades\Bootstrapper\Icon;

trait ValueTrait
{
    protected $value = '';

    public function withContents($value = '')
    {
        if (is_array($value)) {
            $this->value = implode(' ', $value);
        } else {
            $this->value =" $value";
        }
        return $this;
    }

    public function withValue($value = '')
    {
        return $this->withContents($value);
    }

    public function getValue()
    {
        return $this->getContents();
    }

    public function getContents()
    {
        return $this->value;
    }
}