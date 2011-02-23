<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Shout model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Shout extends AutoModeler implements Permission_Interface {

	protected $_table_name = 'shouts';

	protected $_data = array(
		'id'        => null,
		'author_id' => null,
		'shout'     => null,
		'created'   => null,
	);

	protected $_rules = array(
		'author_id' => array('not_empty'),
		'shout'     => array('not_empty', 'max_length' => array(':value', 250)),
	);


	/**
	 * Find latest shouts
	 *
	 * @static
	 * @param   integer  $limit
	 * @return  Database_Result
	 */
	public static function find_latest($limit = 10) {
		return AutoModeler::factory('shout')->load(DB::select()->order_by('id', 'DESC'), $limit);
	}


	/**
	 * Check permission
	 *
	 * @param   string      $permission
	 * @param   Model_User  $user
	 * @return  boolean
	 */
	public function has_permission($permission, $user) {

		// Logged in user has access to everything for now
		return (bool)$user;
	}


	/**
	 * Get object id for Permission
	 *
	 * @return  integer
	 */
	public function get_permission_id() {
		return 0;
	}

}
