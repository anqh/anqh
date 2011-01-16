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
	 * @var  array  User editable fields
	 */
	public static $editable_fields = array(
		'name', 'content'
	);


	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta
			->sorting(array('id' => 'DESC'))
			->fields(array(
				'id' => new Jelly_Field_Primary,
				'name' => new Field_String(array(
					'label' => __('Title'),
					'rules' => array(
						'not_empty'  => null,
						'max_length' => array(200)
					),
				)),
				'content' => new Field_Text(array(
					'label'  => __('Content'),
					'bbcode' => true,
					'rules'  => array(
						'max_length' => array(8192),
					)
				)),

				'created' => new Jelly_Field_Timestamp(array(
					'auto_now_create' => true,
				)),
				'modified' => new Jelly_Field_Timestamp(array(
					'auto_now_update' => true,
				)),
				'modify_count' => new Jelly_Field_Integer,
				'comment_count' => new Jelly_Field_Integer,
				'new_comment_count' => new Jelly_Field_Integer,
				'view_count' => new Jelly_Field_Integer,

				'author' => new Jelly_Field_BelongsTo(array(
					'column'  => 'author_id',
					'foreign' => 'user',
				)),
				'comments' => new Jelly_Field_HasMany(array(
					'foreign' => 'blog_comment'
				))
			));
	}


	/**
	 * Find latest blog entries
	 *
	 * @static
	 * @param   integer  $limit
	 * @return  Jelly_Collection
	 */
	public static function find_new($limit = 10) {
		return Jelly::select('blog_entry')->limit($limit)->execute();
	}


	/**
	 * Get new blog comments count for user.
	 * Return array of ids and counts
	 *
	 * @static
	 * @param   Model_User $user
	 * @return  array
	 */
	public static function find_new_comments(Model_User $user) {
		return Jelly::select('blog_entry')->where('author_id', '=', $user->id)->and_where('new_comment_count', '>', 0)->execute();
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
