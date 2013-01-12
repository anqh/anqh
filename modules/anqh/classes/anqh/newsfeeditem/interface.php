<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * NewsfeedItem Interface
 *
 * @interface
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
interface Anqh_NewsfeedItem_Interface {

	/**
	 * Get newsfeed item as HTML
	 *
	 * @static
	 * @param   Model_NewsfeedItem  $item
	 * @return  string
	 */
	public static function get(Model_NewsfeedItem $item);

}
