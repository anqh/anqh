<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Session
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Session extends Kohana_Session {

	/**
	 * Get current session id
	 *
	 * @return  string
	 */
	public function id() {
		return session_id();
	}

}
