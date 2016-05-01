<?php namespace AwatBayazidi\Foundation\Menu;

use AwatBayazidi\Foundation\HtmlElement\HtmlElement;
use AwatBayazidi\Foundation\Menu\Traits\Activatable as ActivatableTrait;
use AwatBayazidi\Foundation\Menu\Traits\HtmlAttributes;
use AwatBayazidi\Foundation\Menu\Traits\ParentAttributes;

class Link implements Item, Activatable, HasUrl
{
    use ActivatableTrait, HtmlAttributes, ParentAttributes;

    /** @var string */
    protected $text;

    /** @var string */
    protected $url;

    /** @var string */
    protected $prepend = '';

    /** @var string */
    protected $append = '';
    /**
     * @param string $url
     * @param string $text
     */
    protected function __construct($url, $text)
    {
        $this->url = $url;
        $this->text = $text;
        $this->active = false;

        $this->initializeHtmlAttributes();
        $this->initializeParentAttributes();
    }

    /**
     * @param string $url
     * @param string $text
     *
     * @return static
     */
    public static function to($url, $text)
    {
        return new static($url, $text);
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Return a segment of the link's URL. This function works for both absolute
     * and relative URL's. The index is a 1-index based number. Trailing and
     * double slashes are ignored.
     *
     * Example: (new Link('Open Source', 'https://spatie.be/opensource'))->segment(1)
     *      => 'opensource'
     *
     * @param int $index
     *
     * @return string|null
     */
    public function segment($index)
    {
        $path = parse_url($this->url)['path'] ?parse_url($this->url)['path']: '';

        $segments = array_values(
            array_filter(
                explode('/', $path),
                function ($value) {
                    return $value !== '';
                }
            )
        );

        return isset($segments[$index - 1]) ? $segments[$index - 1]: null;
    }

    /**
     * @param string $prefix
     *
     * @return $this
     */
    public function prefix($prefix)
    {
        $this->url = $prefix.'/'.ltrim($this->url, '/');

        return $this;
    }

    /**
     * @return string
     */
    public function render()
    {
        $contents =  HtmlElement::render(
            "a[href={$this->url}]",
            $this->htmlAttributes->toArray(),
            $this->getText()
        );
        return "{$this->prepend}{$contents}{$this->append}";
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    public function append($append)
    {
        $this->append = $append;

        return $this;
    }

    public function prepend($prepend)
    {
        $this->prepend = $prepend;

        return $this;
    }

}
