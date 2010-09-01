<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Index controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Index extends Controller_Template {

	/**
	 * @var  string  Index id is home
	 */
	protected $page_id = 'home';


	/**
	 * Controller default action
	 */
	public function action_index() {
		$this->page_title = __('Welcome to :site', array(':site' => Kohana::config('site.site_name')));

		// Display news feed
		$newsfeed = new NewsFeed(self::$user);
		$newsfeed->max_items = 25;
		Widget::add('main', View_Module::factory('generic/newsfeed', array(
			'newsfeed' => $newsfeed->as_array()
		)));

		// Shout
		$shouts = Jelly::select('shout')->limit(10)->execute();
		Widget::add('side', View_Module::factory('generic/shout', array(
			'mod_title' => __('Shouts'),
			'shouts'    => $shouts,
			'can_shout' => Permission::has(new Model_Shout, Model_Shout::PERMISSION_CREATE),
			'errors'    => array(),
			'values'    => array(),
		)));

	}

}
