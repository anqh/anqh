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
	 * Data: gallery_id, image_id
	 */
	const TYPE_COMMENT = 'comment';

	/**
	 * Comment an entry
	 *
	 * Data: flyer_id, image_id
	 */
	const TYPE_COMMENT_FLYER = 'comment_flyer';


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
					$text = __('commented to an image<br />:gallery', array(
						':gallery' => HTML::anchor(
							Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $image->id, 'action' => '')),
							HTML::chars($gallery->name),
							array('class' => 'icon photo hoverable')
						)
					));
				}
				break;

			case self::TYPE_COMMENT_FLYER:
				$flyer = Jelly::select('flyer')->load($item->data['flyer_id']);
				if ($flyer->loaded()) {
					$text = __('commented to a flyer<br />:flyer', array(
						':flyer' => HTML::anchor(
							Route::get('flyer')->uri(array('id' => $flyer->id)),
							$flyer->name ? HTML::chars($flyer->name) : __('flyer'),
							array('class' => 'icon flyer')
						)
					));
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


	/**
	 * Comment a flyer
	 *
	 * @param  Model_User     $user
	 * @param  Model_Image    $image
	 */
	public static function comment_flyer(Model_User $user = null, Model_Flyer $flyer = null, Model_Image $image = null) {
		if ($user && $flyer && $image) {
			parent::add($user, 'galleries', self::TYPE_COMMENT_FLYER, array('flyer_id' => (int)$flyer->id, 'image_id' => (int)$image->id));
		}
	}

}
