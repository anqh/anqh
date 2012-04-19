<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Image config
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
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

	// Maximum filesize
	'filesize' => '2M',

	// Allowed file types
	'filetypes' => array('jpg', 'jpeg', 'gif', 'png'),

	// Different image sizes
	'sizes' => array(

		// Max width image, default for gallery
		Model_Image::SIZE_WIDE => array(
			'width'  => 940,
			'height' => 680,
			'resize' => array(940, 680), // Wide column
		),

		// Main column
		Model_Image::SIZE_MAIN => array(
			'width'  => 440,
			'height' => 590,
			'resize' => array(440, 590), // Used for side column too, resized in browser
		),

		// Side column
		Model_Image::SIZE_SIDE => array(
			'width'  => 290,
			'height' => 580,
			'resize' => array(290, 580),
		),

		// Thumbnail
		Model_Image::SIZE_THUMBNAIL => array(
			'postfix' => '_t',
			'width'   => 140,
			'height'  => 140,
			'quality' => 90,
			'resize'  => array(140, 140, Image::INVERSE), // Resize to minimum 140x140
			'crop'    => array(140, 140, null, 0),        // Crop to center and top
		),

		// Square
		Model_Image::SIZE_ICON => array(
			'postfix' => '_i',
			'width'   => 50,
			'height'  => 50,
			'quality' => 85,
			'resize'  => array(50, 50, Image::INVERSE), // Resize to minimum 50x50
			'crop'    => array(50, 50, null, 0),        // Crop to center and top
		),

	),

);
