<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Image Exif model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Image_Exif extends AutoModeler_ORM implements Permission_Interface {

	protected $_table_name = 'exifs';

	protected $_data = array(
		'id'            => null,
		'image_id'      => null,
		'make'          => null,
		'model'         => null,
		'exposure'      => null,
		'aperture'      => null,
		'focal'         => null,
		'iso'           => null,
		'taken'         => null,
		'flash'         => null,
		'program'       => null,
		'metering'      => null,
		'latitude'      => null,
		'latitude_ref'  => null,
		'longitude'     => null,
		'longitude_ref' => null,
		'altitude'      => null,
		'altitude_ref'  => null,
		'lens'          => null,
	);

	public $editable_fields = array(
		'id', 'image_id', 'make', 'model', 'exposure', 'aperture', 'focal', 'iso',
		'taken', 'flash', 'program', 'metering', 'latitude', 'latitude_ref',
		'longitude', 'longitude_ref', 'altitude', 'altitude_ref', 'lens'
	);


	/**
	 * Check permission
	 *
	 * @param   string      $permission
	 * @param   Model_User  $user
	 * @return  boolean
	 */
	public function has_permission($permission, $user) {
		switch ($permission) {
			case self::PERMISSION_CREATE:
			case self::PERMISSION_DELETE:
			case self::PERMISSION_READ:
			case self::PERMISSION_UPDATE:
		}

		return false;
	}


	/**
	 * Get the image of current EXIF data.
	 *
	 * @return  Model_Image
	 */
	public function image() {
		return Model_Image::factory($this->image_id);
	}


	/**
	 * Read image exif data and save if found
	 *
	 * @throws  Kohana_Exception
	 * @return  boolean  true if data found
	 */
	public function read() {

		// Image required
		$image = $this->image();
		if (!$image->loaded()) {
			throw new Kohana_Exception('Image required for exif data');
		}

		// Read data and save if found
		$file = $image->get_filename('original');
		$exif = Image_Exif::factory($file)->read();
		if (empty($exif)) {
			return false;
		}

		$this->set_fields($exif);

		return true;
	}


	/**
	 * Creates or updates the current exif data.
	 *
	 * @return  boolean
	 */
	public function save() {

		// If new EXIF data, try to read from image
		if (!$this->loaded()) {
			$this->read();
		}

		// If was new and no exif data was found it will not be saved
		return parent::save();
	}

}
