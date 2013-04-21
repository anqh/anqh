<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * NewsfeedItem Venue
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_NewsfeedItem_Venues extends NewsfeedItem {

	/**
	 * Add a new venue
	 *
	 * Data: venue_id
	 */
	const TYPE_VENUE = 'venue';

	/**
	 * Edit a venue
	 *
	 * Data: venue_id
	 */
	const TYPE_VENUE_EDIT = 'venue_edit';

	/**
	 * @var  array  Aggregate types
	 */
	public static $aggregate = array(self::TYPE_VENUE, self::TYPE_VENUE_EDIT);


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

			case self::TYPE_VENUE:
				$text = $item->is_aggregate() ?  __('added new venues') : __('added a new venue');
		    break;

			case self::TYPE_VENUE_EDIT:
				$text = $item->is_aggregate() ? __('updated venues') : __('updated a venue');
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

			case self::TYPE_VENUE:
			case self::TYPE_VENUE_EDIT:
		    $venue = Model_Venue::factory($item->data['venue_id']);
		    if ($venue->loaded()) {
			    $text = HTML::anchor(
				    Route::model($venue),
			     '<i class="icon-map-marker icon-white"></i> ' . HTML::chars($venue->name),
				    array('class' => 'venue')
			    );
		    }
		    break;

		}

		return $text;
	}


	/**
	 * Add a new venue
	 *
	 * @param  Model_User   $user
	 * @param  Model_Venue  $venue
	 */
	public static function venue(Model_User $user = null, Model_Venue $venue = null) {
		if ($user && $venue) {
			parent::add($user, 'venues', self::TYPE_VENUE, array('venue_id' => (int)$venue->id));
		}
	}


	/**
	 * Edit a venue
	 *
	 * @param  Model_User   $user
	 * @param  Model_Venue  $venue
	 */
	public static function venue_edit(Model_User $user = null, Model_Venue $venue = null) {
		if ($user && $venue) {
			parent::add($user, 'venues', self::TYPE_VENUE_EDIT, array('venue_id' => (int)$venue->id));
		}
	}

}
