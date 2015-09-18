<?php
/**
 * Decorates {@link Image} to return ratio
 *
 * @package bnz
 * @subpackage mysite
 */

use ColorThief\ColorThief;

class ColorThiefImage extends Image {

	private static $db = array(
		'Color' => 'Varchar(7)'
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

	public function getCMSFields() {
		$fields = parent::getCMSFields();

		$pallete = $this->getDominantColorPalette();
		$options = array();

		foreach ($pallete as $color) {
			$options[$color] = "<span style='display:inline-block;width:1em;height:1em;background-color:$color;'></span> $color";
		}

		$fields->AddFieldToTab("Root.Main",
			OptionsetField::create(
				$name = 'Color',
				$title = 'Color',
				$source = $options,
				$value = $this->getField('Color')
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

	private function getDominantColor() {
		$c = ColorThief::getColor(
			$this->getFullPath(),
			$this->config()->quality
		);
		if ($c) {
			$color = self::toHex($c);
		} else {
			$color = $this->config()->fallback_color;
		}
		return $color;
	}

	private function getDominantColorPalette() {
		$c = ColorThief::getPalette(
			$this->getFullPath(),
			$this->config()->quality
		);
		return array_map(array(get_class($this), 'toHex'), $c);
	}

	private static function toHex($color) {
		$hex = dechex(($color[0]<<16)|($color[1]<<8)|$color[2]);
		$hex = str_pad($hex, 6, '0', STR_PAD_LEFT);
		return "#$hex";
	}

}
