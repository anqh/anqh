<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Image config
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
return array(

	// Local image path
	'path' => 'images/',

	// Default upload path, needs write access
	'upload_path' => 'images/upload/',

	// Image url root
	'url'  => '',

	// Prefix for original image
	'postfix_original' => '_o',

	// Default image quality,
	'quality' => 95,

	// Different image sizes
	'sizes' => array(

		// Max width image, default for gallery
		'wide' => array(
			'width'  => 760,
			'height' => 550,
			'resize' => array(760, 570, Image::AUTO), // 760x570 for wide, 440x330 for main, 290x580 for side
		),

		// Main column
		'main' => array(
			'width'  => 440,
			'height' => 590,
			'resize' => array(440, 590, Image::AUTO), // Used for side column too, resized in browser
		),

		// Side column
		'side' => array(
			'width'  => 290,
			'height' => 580,
			'resize' => array(290, 580, Image::AUTO),
		),

		// Thumbnail
		'thumbnail' => array(
			'postfix' => '_t',
			'width'   => 140,
			'height'  => 140,
			'quality' => 90,
			'resize'  => array(140, 140, Image::INVERSE), // Resize to minimum 140x140
			'crop'    => array(140, 140, null, 0),        // Crop to center and top
		),

		// Square
		'icon' => array(
			'postfix' => '_i',
			'width'   => 50,
			'height'  => 50,
			'quality' => 85,
			'resize'  => array(50, 50, Image::INVERSE), // Resize to minimum 50x50
			'crop'    => array(50, 50, null, 0),        // Crop to center and top
		),

	),

);
