<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Image Note model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Image_Note extends Jelly_Model implements Permission_Interface {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id' => new Jelly_Field_Primary,
			'author' => new Jelly_Field_BelongsTo(array(
				'column'  => 'author_id',
				'foreign' => 'user',
			)),
			'image' => new Jelly_Field_BelongsTo,

			'name' => new Jelly_Field_String(array(
				'rules' => array(
					'max_length' => array(30),
					'not_empty'  => null,
				),
			)),
			'user' => new Jelly_Field_BelongsTo(array(
				'allow_null'  => true,
				'empty_value' => null,
			)),
			'x' => new Jelly_Field_Integer,
			'y' => new Jelly_Field_Integer,
			'width' => new Jelly_Field_Integer,
			'height' => new Jelly_Field_Integer,

			'new_comment_count' => new Jelly_Field_Integer,
			'new_note' => new Jelly_Field_Boolean,
			'created' => new Jelly_Field_Timestamp(array(
				'auto_now_create' => true,
			)),
		));
	}


	/**
	 * Get notes with new comments
	 *
	 * @static
	 * @param   Model_User $user
	 * @return  Jelly_Collection
	 */
	public static function find_new_comments(Model_User $user) {
		return Jelly::query('image_note')
			->where('user_id', '=', $user->id)
			->and_where('new_comment_count', '>', 0)
			->select();
	}


	/**
	 * Get new notes
	 *
	 * @static
	 * @param   Model_User $user
	 * @return  Jelly_Collection
	 */
	public static function find_new_notes(Model_User $user) {
		return Jelly::query('image_note')
			->where('user_id', '=', $user->id)
			->and_where('new_note', '=', 1)
			->select();
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
				return (bool)$user;

			case self::PERMISSION_UPDATE:
				return $user && ($user->id == $this->author->id || $user->has_role('admin', 'photo admin'));

			case self::PERMISSION_DELETE:
				return $user && (in_array($user->id, array($this->user->id, $this->author->id)) || $user->has_role('admin', 'photo admin'));

			case self::PERMISSION_READ:
				return true;
		}

		return false;
	}

}
