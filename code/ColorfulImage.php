<?php
/**
 * Decorates {@link Image} to return ratio
 *
 * @package bnz
 * @subpackage mysite
 */

use ColorThief\ColorThief;

class ColorfulImage extends Image {

	private static $db = array(
		'Color' => 'Varchar(6)'
	);

	/**
	 * Overload the Color field to get the dominant color if not set
	 * @return String
	 */
	public function getColor() {
		$color = $this->getField('Color');
		if ($color === NULL) {
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
	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$pallete = $this->dominantColorPalette();

		$options = array();
		foreach ($pallete as $color) {
			$options[$color] = "#$color";
		}

		$fields->AddFieldToTab('Root.Main',
			ColorPaletteField::create(
				$name = 'Color',
				$title = 'Color',
				$source = $options,
				$value = $this->Color
			)
		);

		return $fields;
	}

	/**
	 * Set the Color field to the dominant color by default
	 */
	public function populateDefaults() {
		if ($this->getField('Color') === NULL) {
			$this->Color = $this->dominantColor();
		}
		parent::populateDefaults();
	}

	/**
	 * Write the Color field to get the dominant color if not set
	 */
	public function onBeforeWrite() {
		if ($this->getField('Color') === NULL) {
			$this->Color = $this->dominantColor();
		}
		parent::onBeforeWrite();
	}

	/**
	 * Get the primary dominant color of this Image
	 * @return String
	 */
	public function dominantColor() {
		$c = ColorThief::getColor(
			$sourceImage = $this->getFullPath(),
			$quality = $this->config()->quality
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
			$sourceImage = $this->getFullPath(),
			$colorCount = $colorCount,
			$quality = $this->config()->quality
		);
		return array_map(array(get_class($this), 'array_to_hex'), $c);
	}

	/**
	 * Get contrast color of a passed color
	 * @see https://24ways.org/2010/calculating-color-contrast/
	 * @param {Array|String} $color
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
	 * @param $color (array) (red, blue, green)
	 * @return String
	 */
	protected static function array_to_hex($color) {
		if (empty($color)) {
			return NULL;
		}
		$hex = dechex(($color[0]<<16)|($color[1]<<8)|$color[2]);
		return str_pad($hex, 6, '0', STR_PAD_LEFT);
	}

	/**
	 * Converts a hex string to color array
	 * @param $color (string)
	 * @return (array) (red, blue, green)
	 */
	protected static function hex_to_array($color) {
		$r = hexdec(substr($color, 0, 2));
		$g = hexdec(substr($color, 2, 2));
		$b = hexdec(substr($color, 4, 2));
		return array($r, $g, $b);
	}

}
