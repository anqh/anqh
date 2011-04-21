<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Login model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Login extends AutoModeler {

	protected $_table_name = 'logins';

	protected $_data = array(
		'id'       => null,
		'stamp'    => null,
		'user_id'  => null,
		'username' => null,
		'ip'       => null,
		'hostname' => null,
		'success'  => null,
		'password' => null,
	);


	/**
	 * Log login attempt
	 *
	 * @static
	 * @param  boolean            $success   Was login succesful
	 * @param  string|Model_User  $user      User or username if no user found
	 * @param  boolean            $password  Password given
	 */
	public static function log($success, $user = null, $password = false) {
		$login = new Model_Login();
		try {
			$login->set_fields(array(
				'password' => $password,
				'username' => $user instanceof Model_User ? $user->username : $user,
				'success'  => (bool)$success,
				'ip'       => Request::$client_ip,
				'hostname' => Request::host_name(),
				'stamp'    => time(),
			));
			if ($user instanceof Model_User) {
				$login->user_id  = $user->id;
				$login->username = $user->usernam;
			} else if (is_string($user)) {
				$login->username = $user;
			}
			$login->save();
		} catch (Database_Exception $e) {}
	}

}
