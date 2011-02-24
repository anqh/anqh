<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Newsfeed
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Newsfeed {

	/**
	 * All users newsfeed
	 */
	const ALL = 'default';

	/**
	 * Personal newsfeed
	 */
	const PERSONAL = 'personal';

	/**
	 * Multiple user newsfeed
	 */
	const USERS = 'users';

	/**
	 * @var  integer  Total items loaded
	 */
	protected $_item_count;

	/**
	 * @var  Database_Result  Feed items
	 */
	protected $_items;

	/**
	 * @var  integer  Maximum number of items to fetch
	 */
	public $max_items = 20;

	/**
	 * @var  string  Newsfeed type
	 *
	 * @see  ALL
	 * @see  PERSONAL
	 * @see  USERS
	 */
	protected $_type = self::ALL;

	/**
	 * @var  Model_User  Viewer
	 */
	protected $_user;

	/**
	 * @var  array  Users for multiple user newsfeed
	 */
	public $users = array();


	/**
	 * Create new NewsFeed
	 *
	 * @param  Model_User  $user
	 * @param  string      $type
	 *
	 * @see  ALL
	 * @see  PERSONAL
	 * @see  USERS
	 */
	public function __construct(Model_User $user = null, $type = self::ALL) {
		$this->_user = $user;
		$this->_type = $type;
	}


	/**
	 * Get news feed as array
	 *
	 * @return  array
	 */
	public function as_array() {
		$feed = array();
		foreach ($this->get_items() as $item) {

			// Ignore
			if ($this->_user && $this->_user->is_ignored($item->user_id)) continue;

			$class = 'Newsfeeditem_' . $item->class;
			if (method_exists($class, 'get') && $text = call_user_func(array($class, 'get'), $item)) {
				$feed[] = array(
					'user'  => Model_User::find_user_light($item->user_id),
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
	 * @return  Database_Result
	 */
	protected function get_items() {
		if (empty($this->_items)) {
			switch ($this->_type) {

				// Personal newsfeed
		    case self::PERSONAL:
			    $this->_items = Model_NewsfeedItem::find_items($this->max_items, array($this->_user->id));
	        break;

				// Multiple user newsfeed
				case self::USERS:
					$this->_items = empty($this->users) ? array() : Model_NewsfeedItem::find_items($this->max_items, $this->users);
			    break;

				// All users
		    case self::ALL:
		    default:
					$this->_items = Model_NewsfeedItem::find_items($this->max_items);
			    break;

			}
		}

		return $this->_items;
	}

}
