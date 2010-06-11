<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Tag model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Tag extends Jelly_Model {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta
			->sorting(array('name' => 'ASC'))
			->fields(array(
				'id' => new Field_Primary,
				'group' => new Field_BelongsTo(array(
					'label'   => __('Tag group'),
					'column'  => 'tag_group_id',
					'foreign' => 'tag_group',
					'rules'   => array(
						'not_empty' => array(true)
					)
				)),
				'name' => new Field_String(array(
					'label'   => __('Tag'),
					'rules' => array(
						'not_empty' => array(true),
					),
					'filters' => array(
						'trim' => null,
					),
				)),
				'description' => new Field_String(array(
					'label'   => __('Description'),
					'filters' => array(
						'trim' => null,
					),
				)),
				'author' => new Field_BelongsTo(array(
					'column'  => 'author_id',
					'foreign' => 'user',
				)),
				'created' => new Field_Timestamp(array(
					'auto_now_create' => true,
				)),
		));
	}

}
