<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Image Exif model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Image_Exif extends Jelly_Model implements Permission_Interface {

	public $editable_fields = array(
		'id', 'image', 'make', 'model', 'exposure', 'aperture', 'focal', 'iso',
		'taken', 'flash', 'program', 'metering', 'latitude', 'latitude_ref',
		'longitude', 'longitude_ref', 'altitude', 'altitude_ref', 'lens'
	);


	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta
			->table('exifs')
			->fields(array(
				'id'        => new Field_Primary,
				'image'     => new Field_BelongsTo,
				'make'      => new Field_String,
				'model'     => new Field_String,
				'exposure'  => new Field_String,
				'aperture'  => new Field_String,
				'focal'     => new Field_String,
				'iso'       => new Field_Integer,
				'taken'     => new Field_Timestamp(array(
					'format' => 'Y-m-d H:i:s'
				)),
				'flash'     => new Field_String,
				'program'   => new Field_String,
				'metering'  => new Field_String,
				'latitude'  => new Field_Float,
				'latitude_ref'  => new Field_String,
				'longitude' => new Field_Float,
				'longitude_ref' => new Field_String,
				'altitude'  => new Field_String,
				'altitude_ref'  => new Field_String,
				'lens'      => new Field_String,
			));
	}


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
	 * Read image exif data and save if found
	 *
	 * @throws  Kohana_Exception
	 * @return  boolean  true if data found
	 */
	public function read() {

		// Image required
		$image = $this->image;
		if (!$image->loaded()) {
			throw new Kohana_Exception('Image required for exif data');
		}

		// Read data and save if found
		$file = $image->get_filename('original');
		$exif = Image_Exif::factory($file)->read();
		if (empty($exif)) {
			throw new Kohana_Exception('No exif data found for :file', array(':file' => $file));
		}

		$this->set($exif);
	}


	/**
	 * Creates or updates the current exif data
	 *
	 * If $key is passed, the record will be assumed to exist
	 * and an update will be executed, even if the model isn't loaded().
	 *
	 * @param   mixed  $key
	 * @return  $this
	 */
	public function save($key = null) {

		// If new EXIF data, try to read from image
		if (!$this->loaded() && !$key) {
			$this->read();
		}

		// If was new and no exif data was found it will not be saved
		parent::save($key);
	}

}
