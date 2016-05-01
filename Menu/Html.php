<?php namespace AwatBayazidi\Foundation\Menu;

use AwatBayazidi\Foundation\Menu\Traits\Activatable as ActivatableTrait;
use AwatBayazidi\Foundation\Menu\Traits\ParentAttributes;

class Html implements Item, Activatable
{
    use ActivatableTrait, ParentAttributes;

    /** @var string */
    protected $html;

    /**
     * @param string $html
     */
    protected function __construct($html)
    {
        $this->html = $html;
        $this->active = false;

        $this->initializeParentAttributes();
    }

    /**
     * Create an item containing a chunk of raw html.
     *
     * @param string $html
     *
     * @return static
     */
    public static function raw($html)
    {
        return new static($html);
    }

    /**
     * @return string
     */
    public function getHtml()// : string
    {
        return $this->html;
    }

    /**
     * @return string
     */
    public function render()// : string
    {
        return $this->html;
    }
}
