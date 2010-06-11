<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Newsfeed
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Newsfeed {

	/**
	 * @var  integer  Total items loaded
	 */
	protected $_item_count;

	/**
	 * @var  Jelly_Collection  Feed items
	 */
	protected $_items;

	/**
	 * @var  integer  Maximum number of items to fetch
	 */
	public $max_items;

	/**
	 * @var  Model_User  Newsfeed viewing user
	 */
	protected $_user;


	/**
	 * Create new NewsFeed
	 *
	 * @param  Model_User  $user
	 */
	public function __construct(Model_User $user = null) {
		$this->_user = $user;

		// Set defaults
		$this->max_items = 20;

	}


	/**
	 * Get news feed as array
	 *
	 * @return  array
	 */
	public function as_array() {
		$this->_find_items();
		$feed = array();

		// Print items
		foreach ($this->_items as $item) {
			$class = 'Newsfeeditem_' . $item->class;
			if (method_exists($class, 'get') && $text = call_user_func(array($class, 'get'), $item)) {
				$feed[] = array(
					'user'  => $item->user,
					'stamp' => $item->stamp,
					'text'  => $text
				);
			}
		}

		return $feed;
	}


	/**
	 * Load newsfeed items
	 *
	 * @return  boolean
	 */
	protected function _find_items() {
		if (empty($this->_items)) {
			$this->_items = Jelly::select('newsfeeditem')->limit($this->max_items)->execute();
		}
	}

}
