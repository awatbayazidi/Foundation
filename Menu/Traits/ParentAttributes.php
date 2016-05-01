<?php namespace AwatBayazidi\Foundation\Menu\Traits;

use AwatBayazidi\Foundation\HtmlElement\Attributes;

trait ParentAttributes
{

    protected $parentAttributes;

    protected function initializeParentAttributes()
    {
        $this->parentAttributes = new Attributes();
    }

    /**
     * Return an array of attributes to apply on the parent. This generally means 
     * the attributes that should be applied on the <li> tag.
     *
     * @return array
     */
    public function getParentAttributes() //: array
    {
        return $this->parentAttributes->toArray();
    }

    /**
     * @param string $attribute
     * @param string $value
     *
     * @return $this
     */
    public function setParentAttribute($attribute, $value = '')
    {
        $this->parentAttributes->setAttribute($attribute, $value);

        return $this;
    }

    /**
     * @param string $class
     *
     * @return $this
     */
    public function addParentClass($class)
    {
        $this->parentAttributes->addClass($class);

        return $this;
    }
}
