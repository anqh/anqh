<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog Entry model
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Blog_Entry extends Jelly_Model implements Permission_Interface {

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
		$meta
			->sorting(array('id' => 'DESC'))
			->fields(array(
				'id' => new Field_Primary,
				'name' => new Field_String(array(
					'rules' => array(
						'not_empty'  => null,
						'max_length' => array(200)
					),
				)),
				'entry' => new Field_Text(array(
					'bbcode' => true,
					'rules'  => array(
						'max_length' => array(8192),
					)
				)),

				'created' => new Field_Timestamp(array(
					'auto_now_create' => true,
				)),
				'modified' => new Field_Timestamp(array(
					'auto_now_update' => true,
				)),
				'num_modifies' => new Field_Integer(array(
					'column' => 'modifies'
				)),
				'new_comments' => new Field_Integer(array(
					'column' => 'newcomments',
				)),

				'author' => new Field_BelongsTo(array(
					'column'  => 'author_id',
					'foreign' => 'user',
				)),
				'comments' => new Field_HasMany(array(
					'foreign' => 'blog_comment'
				))
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

			case self::PERMISSION_READ:
		    return true;

			case self::PERMISSION_CREATE:
			case self::PERMISSION_COMMENT:
			case self::PERMISSION_COMMENTS:
		    return (bool)$user;

			case self::PERMISSION_DELETE:
			case self::PERMISSION_UPDATE:
				return $user && ($this->author->id == $user->id || $user->has_role('admin'));

		}

		return false;
	}

}
