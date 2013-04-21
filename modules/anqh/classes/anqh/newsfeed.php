<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Newsfeed
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Newsfeed {

	/** All users newsfeed */
	const ALL = 'default';

	/** Personal newsfeed */
	const PERSONAL = 'personal';

	/** Update old item instead of adding new, parsed via strtotime() */
	const UPDATE_WINDOW = '6 hours ago';

	/** Multiple user newsfeed */
	const USERS = 'users';

	/**
	 * @var  integer  Total items loaded
	 */
	protected $_item_count;

	/**
	 * @var  Model_NewsfeedItem[]  Feed items
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
	 * Aggregate repeating items, daily.
	 *
	 * @param   Model_NewsfeedItem[]  $items
	 * @return  Model_NewsfeedItem[]
	 */
	protected function aggregate($items = null) {
		$aggregated      = array();
		$aggregate_types = array();

		foreach ($items as $item) {

			// Check if item type is aggregateable
			$class = 'Newsfeeditem_' . $item->class;
			if (!isset($aggregate_types[$class])) {
				$aggregate_types[$class] = class_exists($class) ? Arr::get(get_class_vars($class), 'aggregate', array()) : array();
			}
			if (!in_array($item->type, $aggregate_types[$class])) {

				// Not aggregateable
				$aggregated[] = $item;
				continue;

			}

			// Generate key
			$key = $item->user_id . $item->class . $item->type . date('Ymd', $item->stamp);
			if (isset($aggregated[$key])) {

				// Aggregate to existing key

				/** @var  Model_NewsfeedItem  $aggregate */
				$aggregate = clone $aggregated[$key];

				// Make it aggregated pseudo item if necessary
				if (!$aggregate->is_aggregate()) {
					$_aggregate = new Model_NewsfeedItem;
					$_aggregate->is_aggregate(true);
					$_aggregate->set_fields(array(
						'user_id' => $aggregate->user_id,
						'stamp'   => $aggregate->stamp,
						'class'   => $aggregate->class,
						'type'    => $aggregate->type,
						'data'    => array(clone $aggregate),
					));
					$aggregate = $_aggregate;
				}

				// Add current item
				$data      = $aggregate->data;
				$duplicate = false;
				foreach ($data as $_item) {
					if ($_item->data == $item->data) {
						$duplicate = true;
						break;
					}
				}
				if (!$duplicate) {
					$data[]           = $item;
					$aggregate->data  = $data;
					$aggregated[$key] = $aggregate;
				}

			} else {

				// New aggregateable key
				$aggregated[$key] = $item;

			}

		}

		return $aggregated;
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

			$class  = 'Newsfeeditem_' . $item->class;
			if (method_exists($class, 'get') && $text = call_user_func(array($class, 'get'), $item)) {
				$feed[] = array(
					'user'  => Model_User::find_user_light((int)$item->user_id),
					'stamp' => $item->stamp,
					'text'  => $text
				);
			}
		}

		return $feed;
	}


	/**
	 * Load newsfeed items.
	 *
	 * @return  Model_NewsfeedItem[]
	 */
	protected function get_items() {
		if (empty($this->_items)) {
			switch ($this->_type) {

				// Personal newsfeed
		    case self::PERSONAL:
			    $this->_items = array();
			    foreach (Model_NewsfeedItem::find_items($this->max_items, $this->_user ? array($this->_user->id) : null) as $item) {
				    $this->_items[] = $item;
			    }
	        break;

				// Multiple user newsfeed
				case self::USERS:
					$this->_items = $this->aggregate(empty($this->users) ? array() : Model_NewsfeedItem::find_items($this->max_items * 2, $this->users));
			    break;

				// All users
		    case self::ALL:
		    default:
					$this->_items = $this->aggregate(Model_NewsfeedItem::find_items($this->max_items * 2));
			    break;

			}

		}

		return (count($this->_items) > $this->max_items) ? array_slice($this->_items, 0, $this->max_items) : $this->_items;
	}

}
