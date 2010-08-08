<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Session Database class
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Session_Database extends Kohana_Session_Database {

	/**
	 * Get current session id
	 *
	 * @static
	 * @return  string
	 */
	public function id() {
		return $this->_session_id;
	}

}
