<?php

use ColorThief\ColorThief;

class DominantColorImageExtension extends Extension
{
    /**
     * Get contrast color to `Color` field
     * @return String 'black' or 'white'
     */
    public function ContrastColor()
    {
        return self::contrastYIQ($this->dominantColor());
    }

    /**
     * Get the primary dominant color of this Image
     * @return String
     */
    public function DominantColor()
    {
        return self::toHexString($this->dominantColor());
    }

    /**
     * Get the primary dominant color of this Image
     * @return Array (red, green, blue)
     */
    protected function dominantColor()
    {
        $sourceImage = $this->owner->getFullPath();

        $cache = SS_Cache::factory(get_called_class());
        $cacheKey = md5($this->owner->ID . $sourceImage);

        $cached = $cache->load($cacheKey);

        if ($cached) {
            return explode(',', $cached);
        }

        $color = ColorThief::getColor(
            $sourceImage = $sourceImage,
            $quality = Config::inst()->get(get_called_class(), 'quality')
        );

        $cache->save(implode(',', $color), $cacheKey);

        return $color;
    }

    /**
     * Converts a color array into a hex string
     * @param Array $color (red, green, blue)
     * @return String
     */
    protected static function toHexString($color)
    {
        if (empty($color)) return false;
        $hex = dechex(($color[0] << 16) | ($color[1] << 8) | $color[2]);
        return '#' . str_pad($hex, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get contrast color of a passed color
     * @see https://24ways.org/2010/calculating-color-contrast/
     * @param Array $color
     * @return String 'black' or 'white'
     */
    protected static function contrastYIQ($color)
    {
        if (empty($color)) return false;
        $yiq = (($color[0] * 299) + ($color[1] * 587) + ($color[2] * 114)) / 1000;
        return ($yiq >= 128) ? 'black' : 'white';
    }
}
