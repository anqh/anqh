<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Private Forum Topic controller
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Forum_Private extends Controller_Forum_Topic {

	/**
	 * Construct controller
	 */
	public function before() {
		Permission::required(new Model_Forum_Private_Area, Model_Forum_Private_Area::PERMISSION_READ, self::$user);

		$this->private = true;
		$this->tab_id = 'private';

		parent::before();
	}


	/**
	 * Action: post new message
	 */
	public function action_post() {

		// Get private areas
		$areas = Model_Forum_Private_Area::factory()->find_areas();

		// Default to first
		return $this->_edit_topic($areas->current()->id);
	}

}
