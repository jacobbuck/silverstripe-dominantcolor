<?php

use ColorThief\ColorThief;

class DominantColorImageExtension extends Extension
{
    const DARK = 'DARK';
    const LIGHT = 'LIGHT';

    /**
     * Calculation accuracy of the dominant color
     * @var Int
     */
    static public $quality = 10;

    /**
     * Get contrast color to the dominant color
     * @param String $dark Color to return if contrast is dark
     * @param String $light Color to return if contrast is light
     * @return String $dark or $light
     */
    public function ContrastColor($dark = 'black', $light = 'white')
    {
        $contrast = self::contrastYIQ($this->dominantColor());
        if ($contrast === self::DARK) return $dark;
        if ($contrast === self::LIGHT) return $light;
        return false;
    }

    /**
     * Get the primary dominant color of this Image
     * @return String color as hex i.e. #ff0000
     */
    public function DominantColor()
    {
        return self::colorToHex($this->dominantColor());
    }

    /**
     * Determine if the dominant color is dark
     * @return Boolean
     */
    public function IsDark()
    {
        return self::contrastYIQ($this->dominantColor()) === self::LIGHT;
    }

    /**
     * Determine if the dominant color is light
     * @return Boolean
     */
    public function IsLight()
    {
        return self::contrastYIQ($this->dominantColor()) === self::DARK;
    }

    /**
     * Get the primary dominant color of this Image
     * @return Array [red, green, blue]
     */
    protected function dominantColor()
    {
        $sourceImage = $this->owner->getFullPath();

        $cache = SS_Cache::factory(get_called_class());
        $cacheKey = md5($this->owner->ID . $sourceImage);

        $cached = $cache->load($cacheKey);

        if ($cached) return explode(',', $cached);

        $color = ColorThief::getColor(
            $sourceImage = $sourceImage,
            $quality = Config::inst()->get(get_called_class(), 'quality')
        );

        $cache->save(implode(',', $color), $cacheKey);

        return $color;
    }

    /**
     * Converts a color array into a hex string
     * @param Array $color [red, green, blue]
     * @return String color as hex i.e. #ff0000
     */
    protected static function colorToHex($color)
    {
        if (empty($color)) return false;
        $hex = dechex(($color[0] << 16) | ($color[1] << 8) | $color[2]);
        return '#' . str_pad($hex, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get contrast color of a passed color
     * @see https://24ways.org/2010/calculating-color-contrast/
     * @see https://www.w3.org/TR/AERT#color-contrast
     * @param Array $color [red, green, blue]
     * @return String
     */
    protected static function contrastYIQ($color)
    {
        if (empty($color)) return false;
        $yiq = (($color[0] * 299) + ($color[1] * 587) + ($color[2] * 114)) / 1000;
        return ($yiq >= 128) ? DARK : LIGHT;
    }
}
