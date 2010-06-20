<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Gallery model
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Gallery extends Jelly_Model implements Permission_Interface {

	/**
	 * Permission to post comments
	 */
	const PERMISSION_COMMENT = 'comment';

	/**
	 * Permission to read comments
	 */
	const PERMISSION_COMMENTS = 'comments';


	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id' => new Field_Primary,
			'name' => new Field_String(array(
				'rules' => array(
					'not_empty'  => null,
					'min_length' => array(3),
					'max_length' => array(250),
				),
			)),
			'links' => new Field_Text,
			'created' => new Field_Timestamp(array(
				'auto_now_create' => true,
			)),
			'modified' => new Field_Timestamp(array(
				'column' => 'updated',
			)),
			'num_images' => new Field_Integer(array(
				'column' => 'image_count',
			)),

			'event_date' => new Field_Timestamp(array(
				'rules' => array(
					'not_empty' => null,
				),
			)),
			'event' => new Field_BelongsTo,

			'default_image' => new Field_BelongsTo(array(
				'column'  => 'default_image_id',
				'foreign' => 'image',
			)),
			'images' => new Field_ManyToMany,

			'copyright' => new Field_String,
			'dir'       => new Field_String,
			'mainfile'  => new Field_String,
		));
	}


	/**
	 * Find gallery by image id
	 *
	 * @param   integer  $image_id
	 * @return  Model_Gallery
	 */
	public static function find_by_image($image_id) {
		return Jelly::select('gallery')
			->join('galleries_images')
			->on('gallery.:primary_key', '=', 'galleries_images.gallery:foreign_key')
			->where('image_id', '=', (int)$image_id)
			->limit(1)
			->execute();
	}


	/**
	 * Get visible gallery images
	 *
	 * @return  Jelly_Collection
	 */
	public function find_images() {
		return $this->get('images')->where('status', '=', Model_Image::VISIBLE)->order_by('id', 'DESC')->execute();
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
			case self::PERMISSION_DELETE:
		    return $user && $user->has_role('admin');

			case self::PERMISSION_COMMENT:
			case self::PERMISSION_COMMENTS:
			case self::PERMISSION_CREATE:
			case self::PERMISSION_UPDATE:
		    return (bool)$user;

			case self::PERMISSION_READ:
		    return true;
		}

		return false;
	}

}
