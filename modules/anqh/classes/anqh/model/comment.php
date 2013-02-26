<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Comment model
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Model_Comment extends AutoModeler_ORM implements Permission_Interface {

	protected $_data = array(
		'id'        => null,
		'comment'   => null,
		'private'   => 0,
		'author_id' => null,
		'user_id'   => null,
		'created'   => null,
	);

	protected $_rules = array(
		'comment'   => array('not_empty', 'max_length' => array(':value', 300)),
		'author_id' => array('not_empty', 'digit'),
		'private'   => array('in_array' => array(':value', array(0, 1)))
	);

	/**
	 * @var  array
	 */
	public static $editable_fields = array('comment', 'private');


	/**
	 * Add new comment.
	 *
	 * @param   integer  $author_id
	 * @param   integer  $user_id  Target user, e.g. comment receiver
	 * @param   string   $comment
	 * @param   boolean  $private
	 * @param   mixed    $model
	 * @param   AutoModeler
	 * @return  Model_Comment
	 */
	public function add($author_id, $user_id = null, $comment, $private = false, $model = null) {
		$this->author_id = $author_id;
		$this->user_id   = $user_id ? $user_id : null;
		$this->comment   = $comment;
		$this->private   = (bool)$private;
		$this->created   = time();

		$this->save();

		return $this;
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

			case self::PERMISSION_DELETE:
			case self::PERMISSION_UPDATE:
		    return $user && in_array($user->id, array($this->user_id, $this->author_id));

			case self::PERMISSION_READ:
		    return $user && (!$this->private || in_array($user->id, array($this->user_id, $this->author_id)));

		}

		return false;
	}


	/**
	 * Viewer limit for comments.
	 *
	 * @static
	 * @param   Database_Query_Builder_Select  $query
	 * @param   Model_User                     $viewer
	 * @return  Database_Query_Builder_Select
	 */
	public static function query_viewer(Database_Query_Builder_Select $query, Model_User $viewer = null) {
		$query = $query->and_where_open()
			->where('private', '=', 0);

		// Visibility
		if ($viewer) {
			$query = $query
				->or_where('user_id', '=', $viewer->id)
				->or_where('author_id', '=', $viewer->id);
		}

		$query = $query->and_where_close();

		return $query;
	}

}
