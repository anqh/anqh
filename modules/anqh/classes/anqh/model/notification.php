<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Notification model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Notification extends AutoModeler_ORM implements Permission_Interface {

	protected $_table_name = 'notifications';

	protected $_data = array(
		'id'        => null,
		'user_id'   => null,
		'target_id' => null,
		'data_id'   => null,
		'stamp'     => null,
		'class'     => null,
		'type'      => null,
		'text'      => null,
	);

	protected $_rules = array(
		'user_id'   => array('not_empty', 'digit'),
		'target_id' => array('not_empty', 'digit'),
		'data_id'   => array('digit'),
		'class'     => array('max_length' => array(':value', 64)),
		'type'      => array('max_length' => array(':value', 64)),
		'text'      => array('max_length' => array(':value', 4096)),
	);


	/**
	 * Find Notifications for user.
	 *
	 * @static
	 * @param   Model_User  $target
	 * @return  Model_Notification[]
	 */
	public function find_by_target(Model_User $target) {

		// User notifications
		$query = DB::select_array($this->fields())
			->where('target_id', '=', $target->id)
			->order_by('id', 'DESC');

		// Admin notifications
		if ($target->has_role('admin', 'photo moderator')) {
			$query = $query->or_where_open()
				->where('class', '=', Notification_Galleries::CLASS_GALLERIES)
				->and_where('type', '=', Notification_Galleries::TYPE_IMAGE_REPORT)
				->or_where_close();
		}

		return $this->load($query, 0);
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
				return $user && !$user->is_ignored($this->target_id) && !$user->is_ignored($this->target_id, true);

			case self::PERMISSION_DELETE:
				return $user && in_array($user->id, array($this->user_id, $this->target_id));

		}

		return false;
	}

}
