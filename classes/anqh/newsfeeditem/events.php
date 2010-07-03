<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * NewsfeedItem Event
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
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
	 * Add an event to favorites
	 *
	 * Data: event_id
	 */
	const TYPE_FAVORITE = 'favorite';


	/**
	 * Get newsfeed item as HTML
	 *
	 * @static
	 * @param   Model_NewsfeedItem  $item
	 * @return  string
	 */
	public static function get(Model_NewsFeedItem $item) {
		$text = '';

		switch ($item->type) {

			case self::TYPE_EVENT:
				$event = Jelly::select('event')->load($item->data['event_id']);
				if ($event->loaded()) {
					$text = __('added new event :event', array(
						':event' => HTML::anchor(Route::model($event), HTML::chars($event->name), array('class' => 'event'))
					));
				}
				break;

			case self::TYPE_FAVORITE:
				$event = Jelly::select('event')->load($item->data['event_id']);
				if ($event->loaded()) {
					$text = __('added event :event to favorites', array(
						':event' => HTML::anchor(Route::model($event), HTML::chars($event->name), array('class' => 'event'))
					));
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
