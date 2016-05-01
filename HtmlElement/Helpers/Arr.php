<?php namespace AwatBayazidi\Foundation\HtmlElement\Helpers;

class Arr
{
    public static function flatten(array $array)
    {
        $flattened = [];

        foreach ($array as $element) {
            $flattened = array_merge(
                $flattened,
                is_array($element) ? static::flatten($element) : [$element]
            );
        }

        return $flattened;
    }

    public static function map(array $array, callable $mapper)
    {
        return array_map($mapper, $array);
    }

    public static function flatMap(array $array, callable $mapper)
    {
        return static::flatten(static::map($array, $mapper));
    }
}
