<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Newsfeed view.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Newsfeed extends View_Section {

	/** Newsfeed from all users */
	const TYPE_ALL = 'all';

	/** Newsfeed from friends only */
	const TYPE_FRIENDS = 'friends';

	/** Newsfeed from single user only */
	const TYPE_PERSONAL = 'personal';

	public $class     = 'newsfeed border';
	public $id        = 'newsfeed';
	public $tab_style = self::TAB_TAB;

	/**
	 * @var  integer  Item limit
	 */
	public $limit = 25;

	/**
	 * @var  boolean  Smaller newsfeed
	 */
	public $mini = false;

	/**
	 * @var  string  Newsfeed type
	 * @see  TYPE_ALL
	 * @see  TYPE_FRIENDS
	 * @see  TYPE_PERSONAL
	 */
	public $type = self::TYPE_ALL;

	/**
	 * @var  Model_User  For personal
	 */
	public $user;


	/**
	 * Create new newsfeed view.
	 */
	public function __construct() {
		parent::__construct();

		$this->title = __("What's new");
	}


	/**
	 * Render newsfeed.
	 *
	 * @return  string
	 */
	public function content() {
		if ($items = $this->_items()):
			ob_start();

?>

<ul class="media-list">

	<?php foreach ($items as $item): ?>
	<li class="media">
		<div class="pull-left">
			<?= HTML::avatar($item['user']['avatar'], $item['user']['username'], $this->mini) ?>
		</div>
		<div class="media-body">
			<?= HTML::user($item['user']) ?> <small class="ago"><?= HTML::time(Date::short_span($item['stamp'], true, true), $item['stamp']) ?></small>
			<?= $item['text'] ?>
		</div>
	</li>
	<?php endforeach; ?>

</ul>

<?php

			return ob_get_clean();
		endif;

		return __('Whoa, we are totally out of news items for you!');
	}


	/**
	 * Get newsfeed items.
	 *
	 * @return  array
	 */
	protected function _items() {
		switch ($this->type) {

			// Friend newsfeed
			case self::TYPE_FRIENDS:
				$newsfeed = new Newsfeed(self::$_user, Newsfeed::USERS);
				$newsfeed->users = self::$_user->find_friends(0, 0);
		    break;

			// Single user newsfeed
			case self::TYPE_PERSONAL:
				$newsfeed = new NewsFeed($this->user, Newsfeed::PERSONAL);
				break;

			// All users
			case self::TYPE_ALL:
			default:
				$newsfeed = new NewsFeed(self::$_user, Newsfeed::ALL);
		    break;

		}
		$newsfeed->max_items = $this->limit;

		return (array)$newsfeed->as_array();
	}


	/**
	 * Get tabs.
	 *
	 * @return  array
	 */
	public function tabs() {
		$tabs = array();
		if (self::$_user_id && $this->type !== self::TYPE_PERSONAL) {
			$tabs[] = array(
				'selected' => $this->type === self::TYPE_ALL,
				'tab'      => HTML::anchor(Route::url('default') . '?newsfeed=' . self::TYPE_ALL, __('All')),
			);
			$tabs[] = array(
				'selected' => $this->type === self::TYPE_FRIENDS,
				'tab'      => HTML::anchor(Route::url('default') . '?newsfeed=' . self::TYPE_FRIENDS, __('Friends')),
			);
		}

		return $tabs;
	}

}
