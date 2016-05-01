<?php
namespace AwatBayazidi\Foundation\Bootstrapper;

/**
 * Rendered Object abstract class
 *
 * @package Bootstrapper
 */
abstract class RenderedObject
{

    /**
     * @var array
     */
    protected $attributes = [];


    /**
     * Calls the render method on the object. If an exception is thrown,
     * it catches it and displays an error message
     *
     * @return string
     */

    public function __toString()
    {
        try {
            $result = $this->render();
          //  $this->attributes = [];
            return $result;
        } catch (\Exception $e) {
            $class = get_class($e);
            return "<div><p class='bg-warning text-warning'>An exception of"
            . " type <code>{$class}</code> was thrown with the message:"
            . " <code>{$e->getMessage()}</code></div>";
        }
    }

//    function __set($name, $value)
//    {
//        throw new \Exception("Variable ".$name." has not been set.", 1);
//    }
//
    function __get($name)
    {
        if($name == 'value'){
            property_exists($this, 'value') ? $this->value : '' ;
        }else{
            return $this;
        }
        throw new \Exception("Variable ".$name." has not been declared and can not be get.", 1);
    }


    /**
     * Renders the object
     *
     * @return string
     */
    public abstract function render();

    /**
     * Set the attributes of the object
     *
     * @param array $attributes The attributes to use
     * @return $this
     */
    public function withAttributes(array $attributes)
    {
        $this->attributes = array_merge($attributes, $this->attributes);

        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Adds the given classes to attributes
     *
     * @param array $classes
     * @return $this
     */
    public function addClass($classes)
    {
        if (is_array($classes)) {
            $classes = implode(' ', $classes);
        }
        if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = $classes;
        } else {
            $this->attributes['class'] .= " $classes";
        }

        return $this;
    }

//    public function withClass($classes)
//    {
//        if (is_array($classes)) {
//            $this->class .= ' ' . implode(' ', $classes);
//        } else {
//            $this->class .= ' ' . $classes;
//        }
//        return $this;
//
//    }

}