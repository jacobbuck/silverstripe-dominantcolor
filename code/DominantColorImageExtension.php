<?php

use ColorThief\ColorThief;

class DominantColorImageExtension extends Extension
{
    /**
     * Calculation accuracy of the dominant color
     * @var Int
     */
    static public $quality = 10;

    /**
     * Get the primary dominant color of this Image
     * @return String color as hex i.e. #ff0000
     */
    public function DominantColor()
    {
        $sourceImage = $this->owner->getFullPath();

        $cache = SS_Cache::factory(get_called_class());
        $cacheKey = md5($this->owner->ID . $sourceImage);

        $color = $cache->load($cacheKey);

        if ($color) return $color;

        $color = ColorThief::getColor(
            $sourceImage = $sourceImage,
            $quality = Config::inst()->get(get_called_class(), 'quality')
        );

        $color = self::colorToHex($color);

        if ($color) {
            $cache->save($color, $cacheKey);
        }

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
}
