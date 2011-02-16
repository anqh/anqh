<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Tag Group model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Tag_Group extends Jelly_Model {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->sorting(array('name' => 'ASC'));
		$meta->fields(array(
			'id' => new Jelly_Field_Primary,
			'name' => new Jelly_Field_String(array(
				'label'  => __('Group name'),
				'unique' => true,
				'rules'  => array(
					'not_empty' => array(true),
				),
				'filters' => array(
					'trim' => null,
				)
			)),
			'description' => new Jelly_Field_String(array(
				'label'   => __('Description'),
				'filters' => array(
					'trim' => null,
				))
			),
			'author' => new Jelly_Field_BelongsTo(array(
				'column'  => 'author_id',
				'foreign' => 'user',
			)),
			'created' => new Jelly_Field_Timestamp(array(
				'auto_now_create' => true,
			)),
			'tags' => new Jelly_Field_HasMany
		));
	}


	/**
	 * Find tag group by name
	 *
	 * @static
	 * @param   string  $name
	 * @return  Model_Tag_Group
	 */
	public static function find_by_name($name) {
		return Jelly::query('tag_group')
			->where('name', '=', $name)
			->limit(1)
			->select();
	}

}
