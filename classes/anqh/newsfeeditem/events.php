<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * NewsfeedItem Event
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2012 Antti Qvickström
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
				$event = Model_Event::factory($item->data['event_id']);
				if ($event->loaded()) {
					$text = __('added new event<br />:event', array(
						':event' => HTML::anchor(
							Route::model($event),
							'<i class="icon-calendar"></i> ' . HTML::chars($event->name),
							array('class' => 'hoverable')
						)
					));
				}
				break;

			case self::TYPE_EVENT_EDIT:
				$event = Model_Event::factory($item->data['event_id']);
				if ($event->loaded()) {
					$text = __('updated event<br />:event', array(
						':event' => HTML::anchor(
							Route::model($event),
							'<i class="icon-calendar"></i> ' . HTML::chars($event->name),
							array('class' => 'hoverable')
						)
					));
				}
				break;

			case self::TYPE_FAVORITE:
				$event = Model_Event::factory($item->data['event_id']);
				if ($event->loaded()) {
					$text = __('added event to favorites<br />:event', array(
						':event' => HTML::anchor(
							Route::model($event),
							'<i class="icon-heart"></i> ' . HTML::chars($event->name),
							array('class' => 'hoverable')
						)
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
