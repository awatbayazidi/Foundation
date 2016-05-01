<?php
namespace AwatBayazidi\Foundation\Bootstrapper\Traits;


use AwatBayazidi\Abzar\Facades\Bootstrapper\Icon;

trait IconTrait
{

    public function withIcon($icon, $append = true)
    {

        if(strpos($icon,'<') === false){

            $icon = Icon::create($icon);

        }
        if ($append) {
             $this->value = "{$this->value} {$icon}" ;
        } else {
             $this->value = "{$icon} {$this->value}" ;
        }

        return $this;
    }



    public function appendIcon($icon)
    {
        return $this->withIcon($icon, true);
    }



    public function prependIcon($icon)
    {
        return $this->withIcon($icon, false);
    }



    public function getIcon()
    {
        return $this->icon;
    }
}