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
		$newsfeed_type = Arr::get($_GET, 'newsfeed', 'all');
		switch ($newsfeed_type) {

			// Friend newsfeed
			case 'friends':
				$newsfeed = new Newsfeed(self::$user, Newsfeed::USERS);
				$newsfeed->users = self::$user->find_friends(0, 0);
		    break;

			// All users
			case 'all':
			default:
				$newsfeed = new NewsFeed(self::$user, Newsfeed::ALL);
		    break;

		}
		$newsfeed->max_items = 25;

		$view = View_Module::factory('generic/newsfeed', array(
			'newsfeed' => $newsfeed->as_array(),
			'tabs'     => !$this->ajax && (bool)self::$user,
			'tab'      => $newsfeed_type
		));
		if ($this->ajax) {
			echo $view;
			return;
		}
		Widget::add('main', $view);

		// Shout
		Widget::add('side', View_Module::factory('generic/shout', array(
			'mod_title' => __('Shouts'),
			'shouts'    => Model_Shout::find_latest(10),
			'can_shout' => Permission::has(new Model_Shout, Model_Shout::PERMISSION_CREATE),
			'errors'    => array(),
			'values'    => array(),
		)));

		// Online
		Widget::add('side', View_Module::factory('user/online', array(
			'mod_id'    => 'online-users',
			'mod_title' => __('Online'),
			'viewer'    => self::$user,
		)), Widget::BOTTOM);

	}

}
