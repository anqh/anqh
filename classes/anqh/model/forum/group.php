<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Group model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Group extends Jelly_Model implements Permission_Interface {

	/** Permission to create new area */
	const PERMISSION_CREATE_AREA = 'create_area';

	/** Visible group */
	const STATUS_NORMAL = 0;

	/** Hidden group */
	const STATUS_HIDDEN = 1;


	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->sorting(array('sort' => 'ASC'));
		$meta->fields(array(
			'id' => new Jelly_Field_Primary,
			'name' => new Jelly_Field_String(array(
				'label' => __('Group name'),
				'rules' => array(
					'not_empty'  => array(true),
					'max_length' => array(32),
				),
				'filters' => array(
					'trim' => null,
				),
			)),
			'description' => new Jelly_Field_String(array(
				'label' => __('Description'),
				'rules' => array(
					'max_length' => array(250),
				),
				'filters' => array(
					'trim' => null,
				),
			)),
			'created' => new Jelly_Field_Timestamp(array(
				'auto_now_create' => true
			)),
			'sort' => new Jelly_Field_Integer(array(
				'label'   => __('Sort'),
				'default' => 0,
			)),
			'author' => new Jelly_Field_BelongsTo(array(
				'column'  => 'author_id',
				'foreign' => 'user',
			)),
			'status' => new Jelly_Field_Enum(array(
				'label'   => __('Status'),
				'default' => self::STATUS_NORMAL,
				'choices' => array(
					self::STATUS_HIDDEN => 'Hidden',
					self::STATUS_NORMAL => 'Normal',
				),
				'rules' => array(
					'not_empty' => null,
				)
			)),
			'areas' => new Jelly_Field_HasMany(array(
				'foreign' => 'forum_area',
			))
		));
	}


	/**
	 * Get group areas
	 *
	 * @return  Jelly_Collection
	 */
	public function areas() {
		return $this->areas instanceof Jelly_Collection ?
			$this->areas :
			Jelly::query('forum_area')
				->with('last_topic')
				->where('forum_group_id', '=', $this->id)
				->and_where('status', '<>', Model_Forum_Area::STATUS_HIDDEN)
				->select();
	}


	/**
	 * Find all groups
	 *
	 * @static
	 * @return  Jelly_Collection
	 */
	public static function find_all() {
		return Jelly::query('forum_group')
			->where('status', '=', self::STATUS_NORMAL)
			->select();
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
		    if (count($this->areas)) {

			    // Don't delete groups with areas
			    return false;

		    }
			case self::PERMISSION_CREATE:
			case self::PERMISSION_CREATE_AREA:
			case self::PERMISSION_UPDATE:
		    $status = $user && $user->has_role('admin');
		    break;

			case self::PERMISSION_READ:
		    $status = true;
		    break;
		}

		return $status;
	}

}
