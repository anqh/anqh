<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Newsfeed view.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Newsfeed extends View_Section {

	/** Newsfeed from all users */
	const TYPE_ALL = 'all';

	/** Newsfeed from friends only */
	const TYPE_FRIENDS = 'friends';

	public $class = 'newsfeed';
	public $id    = 'newsfeed';

	/**
	 * @var  integer  Item limit
	 */
	public $limit = 25;

	/**
	 * @var  boolean  Smaller newsfeed
	 */
	public $mini = false;

	/**
	 * @var  Newsfeed  Newsfeed type
	 * @see  TYPE_ALL
	 * @see  TYPE_FRIENDS
	 */
	public $type = self::TYPE_ALL;


	/**
	 * Initialize shouts.
	 */
	public function _initialize() {
	}


	/**
	 * Var method for has_tabs.
	 *
	 * @return  boolean
	 */
	public function has_tabs() {
		return (bool)self::$user;
	}


	/**
	 * Newsfeed items.
	 *
	 * @return  array
	 */
	public function items() {
		switch ($this->type) {

			// Friend newsfeed
			case self::TYPE_FRIENDS:
				$newsfeed = new Newsfeed(self::$user, Newsfeed::USERS);
				$newsfeed->users = self::$user->find_friends(0, 0);
		    break;

			// All users
			case self::TYPE_ALL:
			default:
				$newsfeed = new NewsFeed(self::$user, Newsfeed::ALL);
		    break;

		}
		$newsfeed->max_items = $this->limit;

		// Build array
		$items = array();
		if ($newsfeed) {
			foreach ($newsfeed->as_array() as $item) {
				$items[] = array(
					'user'   => HTML::user($item['user']),
					'avatar' => HTML::avatar($item['user']['avatar'], $item['user']['username'], $this->mini),
					'stamp'  => HTML::time(Date::short_span($item['stamp'], true, true), $item['stamp']),
					'text'   => $item['text'],
				);
			}
		}

		return $items;
	}


	/**
	 * Var method for tabs.
	 *
	 * @return  array
	 */
	public function tabs() {
		$tabs = array();
		if ($this->has_tabs()) {
			// @todo: Fix 403 Forbidden unless action index used, .htaccess issue
			$tabs[] = array(
				'selected' => $this->type === self::TYPE_ALL,
				'url'      => Route::url('default') . 'index?newsfeed=' . self::TYPE_ALL,
				'text'     => __('All'),
			);
			$tabs[] = array(
				'selected' => $this->type === self::TYPE_FRIENDS,
				'url'      => Route::url('default') . 'index?newsfeed=' . self::TYPE_FRIENDS,
				'text'     => __('Friends'),
			);
		}

		return $tabs;
	}

}
