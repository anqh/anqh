<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * NewsfeedItem Music
 *
 * @package    Music
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
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
	 * Get newsfeed item as HTML
	 *
	 * @param   Model_NewsfeedItem  $item
	 * @return  string
	 */
	public static function get(Model_NewsfeedItem $item) {
		$text = '';

		switch ($item->type) {

			case self::TYPE_MIX:
				$track = Model_Music_Track::factory($item->data['track_id']);
				if ($track->loaded()) {
					$text = __('added new mixtape<br />:mixtape', array(
						':mixtape' => HTML::anchor(
							Route::model($track),
							'<i class="icon-music icon-white"></i> ' . HTML::chars($track->name),
							array('title' => $track->name)
						)
					));
				}
				break;

			case self::TYPE_TRACK:
				$track = Model_Music_Track::factory($item->data['track_id']);
				if ($track->loaded()) {
					$text = __('added new track<br />:track', array(
						':track' => HTML::anchor(
							Route::model($track),
							'<i class="icon-music icon-white"></i> ' . HTML::chars($track->name),
							array('title' => $track->name)
						)
					));
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
