<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Private Post model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Private_Post extends Model_Forum_Post {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'topic' => new Jelly_Field_BelongsTo(array(
				'column'  => 'forum_topic_id',
				'foreign' => 'forum_private_topic'
			)),
			'parent' => new Jelly_Field_BelongsTo(array(
				'column'  => 'parent_id',
				'foreign' => 'forum_private_post',
			)),
		));

		parent::initialize($meta);
	}

}
