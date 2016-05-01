<?php namespace AwatBayazidi\Foundation\Support;


use AwatBayazidi\Abzar\Collection as BaseCollection;

class Collection extends BaseCollection
{

    public function reset()
    {
        $this->items = [];
        return $this;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function dimensions($dimensions)
    {
        $array = config('uploader.dimensions');
        if(array_has($array, $dimensions)){
             $item = collect(parent::get('dimensions'));
             return collect($item->get($dimensions));
        }
        return null;
    }


    public function __call($method, $parameters)
    {
        $array = config('uploader.dimensions');
        if (array_has($array, $method)) {
            if(count($parameters)>0){
                    return $this->dimensions($method)->get($parameters[0]);
            }else{
                return $this->dimensions($method);
            }
        }
        parent::__call($method, $parameters);
    }

}
