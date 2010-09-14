<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * NewsfeedItem Galleries
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_NewsfeedItem_Galleries extends NewsfeedItem {

	/**
	 * Comment an entry
	 *
	 * Data: entry_id
	 */
	const TYPE_COMMENT = 'comment';


	/**
	 * Get newsfeed item as HTML
	 *
	 * @param   Model_NewsfeedItem  $item
	 * @return  string
	 */
	public static function get(Model_NewsfeedItem $item) {
		$text = '';

		switch ($item->type) {

			case self::TYPE_COMMENT:
				$gallery = Jelly::select('gallery')->load($item->data['gallery_id']);
				$image   = Jelly::select('image')->load($item->data['image_id']);
				if ($gallery->loaded() && $image->loaded()) {
					$text = __('commented to an image in :gallery', array(':gallery' => HTML::anchor(
						Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $image->id, 'action' => '')),
						HTML::chars($gallery->name),
						array('class' => 'hoverable')
					)));
				}
				break;

		}

		return $text;
	}


	/**
	 * Comment an image
	 *
	 * @param  Model_User     $user
	 * @param  Model_Gallery  $gallery
	 * @param  Model_Image    $image
	 */
	public static function comment(Model_User $user = null, Model_Gallery $gallery = null, Model_Image $image = null) {
		if ($user && $gallery && $image) {
			parent::add($user, 'galleries', self::TYPE_COMMENT, array('gallery_id' => (int)$gallery->id, 'image_id' => (int)$image->id));
		}
	}

}
