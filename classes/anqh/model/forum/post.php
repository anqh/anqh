<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Post model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Post extends AutoModeler_ORM implements Permission_Interface {

	protected $_table_name = 'forum_posts';

	protected $_data = array(
		'id'             => null,
		'forum_topic_id' => null,
		'forum_area_id'  => null,
		'parent_id'      => null,

		'author_id'      => null,
		'author_name'    => null,
		'author_ip'      => null,
		'author_host'    => null,

		'created'        => null,
		'modified'       => null,
		'modify_count'   => null,
		'post'           => null,
	);

	protected $_rules = array(
		'forum_topic_id' => array('digit'),
		'forum_area_id'  => array('digit'),
		'parent_id'      => array('digit'),

		'post'           => array('not_empty'),
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

			case self::PERMISSION_READ:
		    return
			    Permission::has($this->topic, Model_Forum_Topic::PERMISSION_READ, $user) // Need read permission for topic
			      && (!$user || !$user->is_ignored($this->author_id));                   // No permission if the author is ignored
		    break;

			// Allow modifying and deleting also from locked topics, fyi
			case self::PERMISSION_UPDATE:
			case self::PERMISSION_DELETE:
		    return $user && ($user->id == $this->author_id || $user->has_role('admin'));

		}

	  return false;
	}

}
