<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * NewsfeedItem Music
 *
 * @package    Music
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_NewsfeedItem_Music extends NewsfeedItem {

	/**
	 * Add new mixtape
	 *
	 * Data: track_id
	 */
	const TYPE_MIX = 'mixtape';

	/**
	 * Add new track
	 *
	 * Data: track_id
	 */
	const TYPE_TRACK = 'track';

	/**
	 * @var  array  Aggregate types
	 */
	public static $aggregate = array(self::TYPE_MIX, self::TYPE_TRACK);


	/**
	 * Get newsfeed item as HTML
	 *
	 * @param   Model_NewsfeedItem  $item
	 * @return  string
	 */
	public static function get(Model_NewsfeedItem $item) {
		$link = $item->is_aggregate() ? implode('<br>', self::get_links($item)) : self::get_link($item);
		if (!$link) {
			return '';
		}

		$text = '';
		switch ($item->type) {

			case self::TYPE_MIX:
				$text = $item->is_aggregate() ? __('added new mixtapes') : __('added a new mixtape');
				break;

			case self::TYPE_TRACK:
				$text = $item->is_aggregate() ? __('added new tracks') : __('added a new track');
				break;

		}

		return $text . '<br>' . $link;
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

			case self::TYPE_MIX:
			case self::TYPE_TRACK:
				$track = Model_Music_Track::factory($item->data['track_id']);
				if ($track->loaded()) {
					$text = HTML::anchor(
						Route::model($track),
						'<i class="fa fa-music"></i> ' . HTML::chars($track->name),
						array('title' => $track->name)
					);
				}
				break;

		}

		return $text;
	}


	/**
	 * Add new music.
	 *
	 * @param  Model_User         $user
	 * @param  Model_Music_Track  $track
	 */
	public static function track(Model_User $user = null, Model_Music_Track $track = null) {
		if ($user && $track) {
			parent::add($user, 'music', $track->type == Model_Music_Track::TYPE_MIX ? self::TYPE_MIX : self::TYPE_TRACK, array('track_id' => (int)$track->id));
		}
	}

}
