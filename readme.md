# silverstripe-dominantcolor

Enhance the Image class by providing the dominant color from an image, and the contrast color of the dominant color.

Uses [Color Thief PHP](https://github.com/ksubileau/color-thief-php).

## Usage

Adds the following methods to `Image`:

### `DominantColor()`

Returns the primary dominant color of this Image as hex (i.e. `'#bada55'`.)

### `IsDark()`

Returns `true` if the primary dominant color is dark.

### `IsLight()`

Returns `true` if the primary dominant color is light.

### `ContrastColor($dark, $light)`

Returns the contrast color to the dominant color as `$dark` if light or `$light` if dark.

Defaults `$dark` to `'black'` and `$light` to `'white'`.

## Requirements

- Silverstripe 3+
- GD, Imagick or Gmagick

## Installation

The recommended way to install is through Composer:

```
composer require jacobbuck/silverstripe-dominantcolor
```
