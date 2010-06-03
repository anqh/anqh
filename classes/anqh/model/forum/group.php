<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Group model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Group extends Jelly_Model {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->sorting(array('sort' => 'ASC'))
			->fields(array(
				'id' => new Field_Primary,
				'name' => new Field_String(array(
					'rules' => array(
						'not_empty'  => array(true),
						'max_length' => array(32),
					)
				)),
				'description' => new Field_String(array(
					'rules' => array(
						'max_length' => array(250),
					)
				)),
				'created' => new Field_Timestamp(array(
					'auto_now_create' => true
				)),
				'sort' => new Field_Integer,
				'author' => new Field_BelongsTo(array(
					'column'  => 'author_id',
					'foreign' => 'user',
				)),
				'areas' => new Field_HasMany(array(
					'foreign' => 'forum_area',
				))
			));
	}

}
