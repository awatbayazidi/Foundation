<?php

namespace AwatBayazidi\Foundation\Support;

class Util
{
    /**
     * @param $tags
     *
     * @return array
     */
    public static function buildTagArray($tags)
    {
        if (is_array($tags)) {
            return $tags;
        }

        if (is_string($tags)) {
            $tags = preg_split('#['.preg_quote(',', '#').']#', $tags, null, PREG_SPLIT_NO_EMPTY);
        }

        return $tags;
    }

    /**
     * @param Taggable $model
     * @param $field
     *
     * @return mixed
     */
    public static function makeTagArray($model, $field)
    {
        return $model->tags()->lists($field, 'tag_id');
    }

    /**
     * @param Taggable $model
     * @param $field
     *
     * @return string
     */
    public static function makeTagList($model, $field)
    {
        return static::joinArray(
            static::makeTagArray($model, $field)->toArray()
        );
    }

    /**
     * @param array $pieces
     *
     * @return string
     */
    public static function joinArray(array $pieces)
    {
        return implode(
            substr(',', 0, 1), $pieces
        );
    }
}
