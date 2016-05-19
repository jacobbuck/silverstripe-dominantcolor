<?php
/**
 * Decorates {@link Image} to return ratio
 *
 * @package bnz
 * @subpackage mysite
 */

use ColorThief\ColorThief;

class ColorfulImageExtension extends DataExtension {

	private static $db = array(
		'Color' => 'Varchar(16)'
	);

	/**
	 * Overload the Color field to get the dominant color if not set
	 * @return String
	 */
	public function getColor() {
		$color = $this->owner->getField('Color');
		if ($color === null) {
			$color = $this->dominantColor();
		}
		return $color;
	}

	/**
	 * Get contrast color to `Color` field
	 * @return String 'black' or 'white'
	 */
	public function getContrastColor() {
		return self::get_contrast_yiq($this->Color);
	}

	/**
	 * Add cms field for Color db field
	 * @return FieldList
	 */
	public function updateCMSFields(FieldList $fields) {
		$color = $this->owner->Color;
		$pallete = $this->dominantColorPalette();

		$options = array();
		$options[$color] = $color;

		foreach ($pallete as $color) {
			$options[$color] = $color;
		}

		$fields->AddFieldToTab('Root.Main',
			ColorPaletteField::create(
				$name = 'Color',
				$title = 'Color',
				$source = $options,
				$value = $this->owner->Color
			)
		);
	}

	/**
	 * Write the Color field to get the dominant color if not set
	 */
	public function onBeforeWrite() {
		if ($this->owner->getField('Color') === null) {
			$this->owner->Color = $this->dominantColor();
		}
		parent::onBeforeWrite();
	}

	/**
	 * Get the primary dominant color of this Image
	 * @return String
	 */
	public function dominantColor() {
		$c = ColorThief::getColor(
			$sourceImage = $this->owner->getFullPath(),
			$quality = Config::inst()->get($this->class, 'quality')
		);
		return self::array_to_hex($c);
	}

	/**
	 * Get the dominant colors of this Image
	 * @param Int $colorCount Count of colors to return
	 * @return Array
	 */
	public function dominantColorPalette($colorCount = 5) {
		$c = ColorThief::getPalette(
			$sourceImage = $this->owner->getFullPath(),
			$colorCount = $colorCount,
			$quality = Config::inst()->get($this->class, 'quality')
		);
		return array_map(array(get_class($this), 'array_to_hex'), $c);
	}

	/**
	 * Get contrast color of a passed color
	 * @see https://24ways.org/2010/calculating-color-contrast/
	 * @param Array|String $color
	 * @return String 'black' or 'white'
	 */
	public static function get_contrast_yiq($color) {
		if (is_string($color)) {
			$color = self::hex_to_array($color);
		}
		$yiq = (($color[0]*299)+($color[1]*587)+($color[2]*114))/1000;
		return ($yiq >= 128) ? 'black' : 'white';
	}

	/**
	 * Converts a color array into a hex string
	 * @param Array $color (red, blue, green)
	 * @return String
	 */
	protected static function array_to_hex($color) {
		if (empty($color)) {
			return null;
		}
		$hex = dechex(($color[0]<<16)|($color[1]<<8)|$color[2]);
		return '#' . str_pad($hex, 6, '0', STR_PAD_LEFT);
	}

	/**
	 * Converts a hex string to color array
	 * @param String $color
	 * @return Array (red, blue, green)
	 */
	protected static function hex_to_array($color) {
		$color = ltrim($color, '#');
		$r = hexdec(substr($color, 0, 2));
		$g = hexdec(substr($color, 2, 2));
		$b = hexdec(substr($color, 4, 2));
		return array($r, $g, $b);
	}

}
