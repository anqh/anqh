<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Post model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Post extends Jelly_Model implements Permission_Interface {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta
			->sorting(array('created' => 'ASC'))
			->fields(array(
				'id'          => new Field_Primary,
				'topic'       => new Field_BelongsTo(array(
					'column'  => 'forum_topic_id',
					'foreign' => 'forum_topic'
				)),
				'area'        => new Field_BelongsTo(array(
					'column'  => 'forum_area_id',
					'foreign' => 'forum_area'
				)),
				'author'      => new Field_BelongsTo(array(
					'column'  => 'author_id',
					'foreign' => 'user',
				)),
				'author_name' => new Field_String,
				'author_ip'   => new Field_String,
				'author_host' => new Field_String,
				'modifies'    => new Field_Integer,
				'parent'      => new Field_BelongsTo(array(
					'column'  => 'parent_id',
					'foreign' => 'forum_post',
				)),
				'created'     => new Field_Timestamp(array(
					'auto_now_create' => true,
				)),
				'modified'    => new Field_Timestamp,
				'post'        => new Field_Text(array(
					'label'  => __('Post'),
					'bbcode' => true,
					'rules' => array(
						'not_empty' => array(true),
					),
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
		switch ($permission) {

			case self::PERMISSION_READ:
		    return
			    Permission::has($this->topic, Model_Forum_Topic::PERMISSION_READ, $user) // Need read permission for topic
			      && (!$user || !$user->is_ignored($this->original('author')));          // No permission if the author is ignored
		    break;

			// Allow modifying and deleting also from locked topics, fyi
			case self::PERMISSION_UPDATE:
			case self::PERMISSION_DELETE:
		    return $user && ($user->id == $this->original('author') || $user->has_role('admin'));

		}

	  return false;
	}

}
