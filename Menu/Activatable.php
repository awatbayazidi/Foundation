<?php namespace AwatBayazidi\Foundation\Menu;

interface Activatable
{
    /**
     * @return $this
     */
    public function setActive();

    /**
     * @return $this
     */
    public function setInActive();
}
