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
	 * @return string
	 */
	public function getColor() {
		$color = $this->getField('Color');
		if ($color === NULL) {
			$color = $this->getDominantColor();
		}
		return $color;
	}

	public function getContrastColor() {
		return self::getContrastYIQ($this->Color);
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$pallete = $this->getDominantColorPalette();

		$options = array();
		foreach ($pallete as $color) {
			$contrast = self::getContrastYIQ($color);
			$options[$color] = "<span style='display:inline-block;padding:0.2em 0.4em;background-color:#$color;color:$contrast'>$color</span>";
		}

		$fields->AddFieldToTab("Root.Main",
			OptionsetField::create(
				$name = 'Color',
				$title = 'Color',
				$source = $options,
				$value = $this->Color
			)
		);

		return $fields;
	}

	public function populateDefaults() {
		if ($this->ID && $this->getField('Color') === NULL) {
			$this->Color = $this->getDominantColor();
			$this->write();
		}
		parent::populateDefaults();
	}

	public function onBeforeWrite() {
		if ($this->getField('Color') === NULL) {
			$this->Color = $this->getDominantColor();
		}
		parent::onBeforeWrite();
	}

	protected function getDominantColor() {
		$c = ColorThief::getColor(
			$sourceImage = $this->getFullPath(),
			$quality = $this->config()->quality
		);
		return self::arrayToHex($c);
	}

	protected function getDominantColorPalette($colorCount = 5) {
		$c = ColorThief::getPalette(
			$sourceImage = $this->getFullPath(),
			$colorCount = $colorCount,
			$quality = $this->config()->quality
		);
		return array_map(array(get_class($this), 'arrayToHex'), $c);
	}

	/**
	 *
	 * @see https://24ways.org/2010/calculating-color-contrast/
	 */
	protected static function getContrastYIQ($color) {
		$color = self::hexToArray($color);
		$yiq = (($color[0]*299)+($color[1]*587)+($color[2]*114))/1000;
		return ($yiq >= 128) ? 'black' : 'white';
	}

	/**
	 * Converts a color array into a hex string
	 * @param $color (array) (red, blue, green)
	 * @return (string)
	 */
	private static function arrayToHex($color) {
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
	private static function hexToArray($color) {
		$r = hexdec(substr($color, 0, 2));
		$g = hexdec(substr($color, 2, 2));
		$b = hexdec(substr($color, 4, 2));
		return array($r, $g, $b);
	}

}
