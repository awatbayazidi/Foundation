<?php namespace AwatBayazidi\Foundation\Menu\Traits;

trait Activatable
{
    /** @var bool */
    protected $active = false;

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return $this
     */
    public function setActive()
    {
        $this->active = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function setInactive()
    {
        $this->active = false;

        return $this;
    }
}
