<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * NewsfeedItem Venue
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_NewsfeedItem_Venue {

	/**
	 * Changes new default image
	 *
	 * Data: venue_id, image_id
	 */
	const TYPE_DEFAULT_IMAGE = 'default_image';


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

			case self::TYPE_DEFAULT_IMAGE:
		    $image = Jelly::select('image')->load($item->data['image_id']);
		    if ($image->loaded()) {
			    $text = __('changed their default image');
		    }
		    break;

		}

		return $text;
	}

	/**
	 * Change default image
	 *
	 * @static
	 * @param  Model_User   $user
	 * @param  Model_Venue  $venue
	 * @param  Model_Image  $image
	 */
	public static function default_image(Model_User $user = null, Model_Venue $venue = null, Model_Image $image = null) {
		if ($user && $image && $venue) {
			parent::add($user, 'venue', self::TYPE_DEFAULT_IMAGE, array('venue_id' => $venue->id, 'image_id' => $image->id));
		}
	}

}
