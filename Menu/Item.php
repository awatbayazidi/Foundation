<?php namespace AwatBayazidi\Foundation\Menu;

interface Item
{
    /**
     * Determine whether the item is active or not.
     *
     * @return bool
     */
    public function isActive();

    /**
     * Return an array of attributes to apply on the parent. This generally means the attributes
     * that should be applied on the <li> tag.
     *
     * @return array
     */
    public function getParentAttributes();

    /**
     * Render the item in html.
     *
     * @return string
     */
    public function render();
}
