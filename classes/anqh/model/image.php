<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Image model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Image extends Jelly_Model implements Permission_Interface {

	const DELETED      = 'd';
	const HIDDEN       = 'h';
	const NOT_ACCEPTED = 'n';
	const VISIBLE      = 'v';

	const NORMAL   = 'normal';
	const ORIGINAL = '';
	const THUMB    = 'thumb';

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id'       => new Field_Primary,
			'status'   => new Field_Enum(array(
				'default' => self::VISIBLE,
				'choices' => array(
					self::DELETED      => __('Deleted'),
					self::HIDDEN       => __('Hidden'),
					self::NOT_ACCEPTED => __('Not accepted'),
					self::VISIBLE      => __('Visible'),
				)
			)),
			'description' => new Field_String,
			'format'      => new Field_String,

			'author'   => new Field_BelongsTo(array(
				'column'  => 'author_id',
				'foreign' => 'user',
			)),
			'exif'     => new Field_HasOne,
			'comments' => new Field_HasMany(array(
				'foreign' => 'image_comment',
			)),
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
		$status = false;

		switch ($permission) {
			case self::PERMISSION_CREATE:
			case self::PERMISSION_DELETE:
			case self::PERMISSION_READ:
			case self::PERMISSION_UPDATE:
		}

		return $status;
	}


	/**
	 * Build image URL
	 *
	 * @param   string  $size
	 * @return  string
	 *
	 * @see  NORMAL
	 * @see  THUMB
	 */
	public function url($size = self::NORMAL) {
		$url = '';

		if ($this->loaded()) {
			$path = URL::id($this->id);

			// Postfix filename if necessary
			$postfix = in_array($size, array(self::NORMAL, self::THUMB)) ? '_' . substr($size, 0, 1) : '';
			$filename = $this->id . $postfix . '.' . $this->format;

			$url = 'http://' . Kohana::config('site.image_server') . '/' . $path . '/' . $filename;
		}

		return $url;
	}

}
