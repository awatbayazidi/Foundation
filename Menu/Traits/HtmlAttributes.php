<?php namespace AwatBayazidi\Foundation\Menu\Traits;

use AwatBayazidi\Foundation\HtmlElement\Attributes;

trait HtmlAttributes
{

    protected $htmlAttributes;

    protected function initializeHtmlAttributes()
    {
        $this->htmlAttributes = new Attributes();
    }

    /**
     * @param string $attribute
     * @param string $value
     *
     * @return $this
     */
    public function setAttribute($attribute, $value = '')
    {
        $this->htmlAttributes->setAttribute($attribute, $value);

        return $this;
    }

    /**
     * @param string $class
     *
     * @return $this
     */
    public function addClass($class)
    {
        $this->htmlAttributes->addClass($class);

        return $this;
    }
}
