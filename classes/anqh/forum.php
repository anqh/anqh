<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Forum {

	/**
	 * Forum version
	 */
	const VERSION = 0.5;


	/**
	 * Get private messages URL
	 *
	 * @static
	 * @return  string
	 */
	public static function private_messages_url() {
		return Route::get('forum_private_area')->uri();
	}

}
