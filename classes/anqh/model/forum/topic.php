<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Topic model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Topic extends Jelly_Model implements Permission_Interface {

	/**
	 * Permission to post reply to topic
	 */
	const PERMISSION_POST = 'post';


	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta
			->fields(array(
				'id'          => new Field_Primary,
				'area'        => new Field_BelongsTo(array(
					'foreign' => 'forum_area',
					'rules'   => array(
						'not_empty' => array(true),
					)
				)),
				'name'        => new Field_String(array(
					'rules' => array(
						'not_empty' => array(true),
						'max_length' => array(200),
					)
				)),
				'old_name'    => new Field_String,
				'author'      => new Field_BelongsTo(array(
					'column'  => 'author_id',
					'foreign' => 'user',
				)),
				'author_name' => new Field_String,
				'created' => new Field_Timestamp(array(
					'auto_now_create' => true,
				)),
				'first_post' => new Field_BelongsTo(array(
					'column' => 'first_post_id',
					'foreign' => 'forum_post',
				)),
				'last_post'   => new Field_BelongsTo(array(
					'column'  => 'last_post_id',
					'foreign' => 'forum_post',
				)),
				'last_posted' => new Field_Integer,
				'last_poster' => new Field_String,
				'type'        => new Field_Integer,
				'sticky'      => new Field_Boolean,
				'read_only'   => new Field_Boolean,
				'num_reads'   => new Field_Integer(array(
					'column' => 'reads'
				)),
				'num_posts'   => new Field_Integer(array(
					'column' => 'posts',
				)),
				'votes'       => new Field_Integer,
				'points'      => new Field_Integer,
				'bind_id'     => new Field_Integer,
				'posts'       => new Field_HasMany(array(
					'foreign' => 'forum_post',
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
		$status = false;

		switch ($permission) {
			case self::PERMISSION_DELETE:
		    break;

			case self::PERMISSION_READ:
				$status = Permission::has($this->area, Model_Forum_Area::PERMISSION_READ, $user);
		    break;

			case self::PERMISSION_UPDATE:
		    break;
		}

		return $status;
	}

}
