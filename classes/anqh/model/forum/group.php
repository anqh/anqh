<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Group model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Group extends AutoModeler_ORM implements Permission_Interface {

	/** Permission to create new area */
	const PERMISSION_CREATE_AREA = 'create_area';

	/** Visible group */
	const STATUS_NORMAL = 0;

	/** Hidden group */
	const STATUS_HIDDEN = 1;

	protected $_table_name = 'forum_groups';

	protected $_data = array(
		'id'          => null,
		'name'        => null,
		'description' => null,
		'created'     => null,
		'sort'        => 0,
		'author_id'   => null,
		'status'      => self::STATUS_NORMAL,
	);

	protected $_rules = array(
		'name'        => array('not_empty', 'max_length' => array(':value', 32)),
		'description' => array('max_length' => array(':value', 250)),
		'status'      => array('not_empty', 'in_array' => array(':value', array(self::STATUS_HIDDEN, self::STATUS_NORMAL))),
	);

	protected $_has_many = array(
		'forum_areas'
	);

	/**
	 * Get group areas
	 *
	 * @return  Model_Forum_Area[]
	 */
	public function areas() {
		return $this->find_related(
			'forum_areas',
			DB::select_array(Model_Forum_Area::factory()->fields())
				->where('status', '<>', Model_Forum_Area::STATUS_HIDDEN)
		);
	}


	/**
	 * Find all groups
	 *
	 * @return  Model_Forum_Group[]
	 */
	public function find_all() {
		return $this->load(
			DB::select_array($this->fields())
				->where('status', '=', self::STATUS_NORMAL)
				->order_by('sort', 'ASC'),
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
		$status = false;

		switch ($permission) {
			case self::PERMISSION_DELETE:
		    if (count($this->areas())) {

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
