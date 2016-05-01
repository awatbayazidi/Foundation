<?php
namespace AwatBayazidi\Foundation\Asset;

use InvalidArgumentException;
use AwatBayazidi\Abzar\Utilities\assistant;
class Asset
{
    const REGEX_CSS = '/\.css$/i';
    const REGEX_JS = '/\.js$/i';
    const REGEX_MINIFIED_CSS = '/[.-]min\.css$/i';
    const REGEX_MINIFIED_JS = '/[.-]min\.js$/i';
    const REGEX_EXTERNAL_URL = '/^((https?:)\/\/|data:)/i';

    const TYPE_CSS  = 'css';
    const TYPE_JS   = 'js';
    const TYPE_AUTO = 'auto';
    const GROUP_DEFAULT  = '';

    private $css_assets = array();
    private $js_assets = array();
    private $collections;
    private $assistant;
    private $secure = false;

    const FORMAT_CSS_LINK = '<link%s rel="stylesheet" href="%s">';
    const FORMAT_JS_LINK  = '<script%s src="%s"></script>';

    public function __construct() {
        $this->setCollections(config('mazhool.collections'));
        $this->assistant = new assistant();

    }

    public function setCollections($collections) {
        $this->collections = $collections;
        return $this;
    }

    public function setSecure($secure = false) {
        $this->secure = $secure;
        return $this;
    }


    private function checkGroupExists($group) {
        if (!array_key_exists($group, $this->css_assets)) {
            $this->css_assets[$group] = [];
        }
        if (!array_key_exists($group, $this->js_assets)) {
            $this->js_assets[$group] = [];
        }
    }

    public function add($asset, $group = self::GROUP_DEFAULT , $type = self::TYPE_AUTO) {
        $this->checkGroupExists($group);

        if (is_array($asset)) {
            foreach ($asset as $a) {
                $this->add($a, $group, $type);
            }
        } elseif ($type === self::TYPE_CSS || $type === self::TYPE_AUTO && preg_match(self::REGEX_CSS, $asset)) {
            if (!in_array($asset, $this->css_assets[$group])) {
                $this->css_assets[$group][] = $asset;
            }
        } elseif ($type === self::TYPE_JS || $type === self::TYPE_AUTO && preg_match(self::REGEX_JS, $asset)) {
            if (!in_array($asset, $this->js_assets[$group])) {
                $this->js_assets[$group][] = $asset;
            }
        } elseif (array_key_exists($asset, $this->collections)) {
            $this->add($this->collections[$asset], $group, $type);
        } else {
            throw new InvalidArgumentException('Unknown asset type: ' . $asset);
        }
        return $this;
    }

    public function mm(){
        return $this;
    }

    public function css($group = self::GROUP_DEFAULT, array $attributes = []) {
        $this->checkGroupExists($group);
        if( ! array_key_exists('type', $attributes))
            $attributes['type'] = 'text/css';

        if( ! array_key_exists('rel', $attributes))
            $attributes['rel'] = 'stylesheet';
        return $this->processAssets($attributes, $this->css_assets[$group], self::FORMAT_CSS_LINK);
    }

    public function js($group = self::GROUP_DEFAULT, array $attributes = []) {
        $this->checkGroupExists($group);
        if( ! array_key_exists('type', $attributes))
            $attributes['type'] = 'text/javascript';

        return $this->processAssets($attributes, $this->js_assets[$group], self::FORMAT_JS_LINK);
    }

    private function processAssets(array $attributes, array $assets,$format) {
        $all_assets = [];

        foreach ($assets as $asset) {
            if ($this->assistant->isAbsoluteUrl($asset)) {
                $hash = $asset;
            } else {
                $hash = asset($asset, $this->secure);
            }
            $all_assets[] = $hash;
        }

        // The file name of our pipelined asset.
        $hash = implode('', $all_assets);

        return $this->htmlLinks($all_assets, $format, $attributes);
    }

    private function htmlLinks($assets, $format, $attributes) {

        $html_attributes =$this->assistant->convertAttributesToHtml($attributes);

        $html_links = '';
        foreach ($assets as $asset) {
            $html_links .= sprintf($format, $html_attributes,$asset)."\n";
        }
        return $html_links;
    }

    public function reset()
    {
        return $this->resetCss()->resetJs();
    }


    public function resetCss()
    {
        $this->css_assets = array();

        return $this;
    }


    public function resetJs()
    {
        $this->js_assets = array();

        return $this;
    }
}