<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog Entry model
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Blog_Entry extends AutoModeler_ORM implements Permission_Interface {

	/**
	 * Permission to post comments
	 */
	const PERMISSION_COMMENT = 'comment';

	/**
	 * Permission to read comments
	 */
	const PERMISSION_COMMENTS = 'comments';

	protected $_table_name = 'blog_entries';

	protected $_data = array(
		'id'                => null,
		'name'              => null,
		'content'           => null,
		'created'           => null,
		'modified'          => null,
		'modify_count'      => null,
		'comment_count'     => null,
		'new_comment_count' => null,
		'view_count'        => null,
		'author_id'         => null,
	);

	protected $_rules = array(
		'name'    => array('not_empty', 'max_length' => array(':value', 200)),
		'content' => array('not_empty', 'max_length' => array(':value', 8192)),
	);


	/**
	 * @var  array  User editable fields
	 */
	public static $editable_fields = array(
		'name', 'content'
	);


	/**
	 * Get blog comments
	 *
	 * @param   Model_User  $viewer
	 * @return  Database_Result
	 */
	public function comments(Model_User $viewer = null) {
		$query = Model_Comment::query_viewer(DB::select_array(Model_Blog_Comment::factory()->fields()), $viewer);

		return $this->find_related('blog_comments', $query);
	}


	/**
	 * Find latest blog entries.
	 *
	 * @param   integer  $limit
	 * @return  Database_Result
	 */
	public function find_new($limit = 10) {
		return $this->load(
			DB::select_array($this->fields())
				->order_by('id', 'DESC'),
			$limit
		);
	}


	/**
	 * Get new blog comments count for user.
	 *
	 * @param   Model_User  $user
	 * @return  Database_Result
	 */
	public function find_new_comments(Model_User $user) {
		return $this->load(
			DB::select_array($this->fields())
				->where('author_id', '=', $user->id)
				->and_where('new_comment_count', '>', 0),
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

			case self::PERMISSION_READ:
		    return true;

			case self::PERMISSION_CREATE:
			case self::PERMISSION_COMMENT:
			case self::PERMISSION_COMMENTS:
		    return (bool)$user;

			case self::PERMISSION_DELETE:
			case self::PERMISSION_UPDATE:
				return $user && ($this->author_id == $user->id || $user->has_role('admin'));

		}

		return false;
	}

}
