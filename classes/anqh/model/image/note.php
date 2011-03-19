<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Image Note model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Image_Note extends AutoModeler_ORM implements Permission_Interface {

	protected $_table_name = 'image_notes';

	protected $_data = array(
		'id'                => null,
		'author_id'         => null,
		'image_id'          => null,

		'name'              => null,
		'user_id'           => null,
		'x'                 => null,
		'y'                 => null,
		'width'             => null,
		'height'            => null,

		'new_comment_count' => null,
		'new_note'          => null,
		'created'           => null,
	);

	protected $_rules = array(
		'name'              => array('not_empty', 'max_length' => array(':value', 30)),
		'x'                 => array('digit'),
		'y'                 => array('digit'),
		'width'             => array('digit'),
		'height'            => array('digit'),
	);


	/**
	 * Get notes with new comments
	 *
	 * @param   Model_User  $user
	 * @return  Database_Result
	 */
	public function find_new_comments(Model_User $user) {
		return $this->load(
			DB::select_array($this->fields())
				->where('user_id', '=', $user->id)
				->and_where('new_comment_count', '>', 0),
			null
		);
	}


	/**
	 * Get new notes
	 *
	 * @param   Model_User  $user
	 * @return  Database_Result
	 */
	public function find_new_notes(Model_User $user) {
		return $this->load(
			DB::select_array($this->fields())
				->where('user_id', '=', $user->id)
				->and_where('new_note', '>', 0),
			null
		);
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
				return $user && ($user->id == $this->author_id || $user->has_role('admin', 'photo admin'));

			case self::PERMISSION_DELETE:
				return $user && (in_array($user->id, array($this->user_id, $this->author_id)) || $user->has_role('admin', 'photo admin'));

			case self::PERMISSION_READ:
				return true;
		}

		return false;
	}


	/**
	 * Get note image
	 *
	 * @return  Model_Image
	 */
	public function image() {
		try {
			return $this->image_id ? Model_Image::factory($this->image_id) : null;
		} catch (AutoModeler_Exception $e) {
			return null;
		}
	}


	/**
	 * Get note target user light array
	 *
	 * @return  array
	 */
	public function user() {
		try {
			return $this->user_id ? Model_User::find_user_light($this->user_id) : null;
		} catch (AutoModeler_Exception $e) {
			return null;
		}
	}

}
