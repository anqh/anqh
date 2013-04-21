<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * NewsfeedItem Event
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_NewsfeedItem_Events extends NewsfeedItem {

	/**
	 * Add a new event
	 *
	 * Data: event_id
	 */
	const TYPE_EVENT = 'event';

	/**
	 * Edit an event
	 *
	 * Data: event_id
	 */
	const TYPE_EVENT_EDIT = 'event_edit';

	/**
	 * Add an event to favorites
	 *
	 * Data: event_id
	 */
	const TYPE_FAVORITE = 'favorite';

	/**
	 * @var  array  Aggregate types
	 */
	public static $aggregate = array(self::TYPE_EVENT, self::TYPE_EVENT_EDIT, self::TYPE_FAVORITE);


	/**
	 * Get newsfeed item as HTML
	 *
	 * @static
	 * @param   Model_NewsfeedItem  $item
	 * @return  string
	 */
	public static function get(Model_NewsFeedItem $item) {
		$link = $item->is_aggregate() ? Text::implode_and(self::get_links($item)) : self::get_link($item);
		if (!$link) {
			return '';
		}

		$text = '';
		switch ($item->type) {

			case self::TYPE_EVENT:
				$text = $item->is_aggregate() ? __('added new events') : __('added a new event');
				break;

			case self::TYPE_EVENT_EDIT:
				$text = $item->is_aggregate() ? __('updated events') : __('updated an event');
				break;

			case self::TYPE_FAVORITE:
				$text = $item->is_aggregate() ? __('added events to favorites') : __('added an events to favorite');
				break;

		}

		return $text . '<br />' . $link;
	}


	/**
	 * Get anchor to newsfeed item target.
	 *
	 * @static
	 * @param   Model_NewsfeedItem  $item
	 * @return  string
	 */
	public static function get_link(Model_NewsfeedItem $item) {
		$text = '';

		switch ($item->type) {

			case self::TYPE_EVENT:
			case self::TYPE_EVENT_EDIT:
			case self::TYPE_FAVORITE:
				$event = Model_Event::factory($item->data['event_id']);
				if ($event->loaded()) {
					$text = HTML::anchor(
						Route::model($event),
						'<i class="icon-calendar icon-white"></i> ' . HTML::chars($event->name),
						array('class' => 'hoverable')
					);
				}
				break;

		}

		return $text;
	}


	/**
	 * Add a new event
	 *
	 * @param  Model_User   $user
	 * @param  Model_Event  $event
	 */
	public static function event(Model_User $user = null, Model_Event $event = null) {
		if ($user && $event) {
			parent::add($user, 'events', self::TYPE_EVENT, array('event_id' => (int)$event->id));
		}
	}


	/**
	 * Edit an event
	 *
	 * @param  Model_User   $user
	 * @param  Model_Event  $event
	 */
	public static function event_edit(Model_User $user = null, Model_Event $event = null) {
		if ($user && $event) {
			parent::add($user, 'events', self::TYPE_EVENT_EDIT, array('event_id' => (int)$event->id));
		}
	}


	/**
	 * Add an event to favorites
	 *
	 * @param  Model_User   $user
	 * @param  Model_Event  $event
	 */
	public static function favorite(Model_User $user = null, Model_Event $event = null) {
		if ($user && $event) {
			parent::add($user, 'events', self::TYPE_FAVORITE, array('event_id' => (int)$event->id));
		}
	}

}
