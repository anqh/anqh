<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Forum controller
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Forum extends Controller_Template {

	protected $_config;


	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		$this->page_title = __('Forum');
		$this->tabs = array(
			'index' => array('link' => Route::get('forum')->uri(), 'text' => __('New posts')),
			'areas' => array('link' => Route::get('forum')->uri(array('action' => 'areas')), 'text' => __('Areas'))
		);
	}


	/**
	 * Controller default action, latest posts
	 */
	public function action_index() {
		$this->tab_id = 'index';

		Widget::add('main', View_Module::factory('forum/topics', array(
			'mod_class' => 'topics articles',
			'topics'    => Jelly::select('forum_topic')->latest()->execute()
		)));
	}

}
