<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * NewsfeedItem Galleries
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2013 Antti Qvickström
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
	 * Edit a flyer
	 *
	 * Data: flyer_id
	 */
	const TYPE_FLYER_EDIT = 'flyer_edit';

	/**
	 * Tag a user to image
	 *
	 * Data: gallery_id, image_id, user_id
	 */
	const TYPE_NOTE = 'note';

	/**
	 * @var  array  Aggregate types
	 */
	public static $aggregate = array(self::TYPE_COMMENT, self::TYPE_COMMENT_FLYER, self::TYPE_FLYER_EDIT);


	/**
	 * Get newsfeed item as HTML
	 *
	 * @param   Model_NewsfeedItem  $item
	 * @return  string
	 */
	public static function get(Model_NewsfeedItem $item) {
		$link = $item->is_aggregate() ? implode('<br />', self::get_links($item)) : self::get_link($item);
		if (!$link) {
			return '';
		}

		$text = '';
		switch ($item->type) {

			case self::TYPE_COMMENT:
				$text = $item->is_aggregate() ? __('commented photos') : __('commented a photo');
				break;

			case self::TYPE_COMMENT_FLYER:
				$text = $item->is_aggregate() ? __('commented flyers') : __('commented a flyer');
				break;

			case self::TYPE_FLYER_EDIT:
				$text = $item->is_aggregate() ? __('updated flyers') : __('updated a flyer');
				break;

			case self::TYPE_NOTE:
				$user = Model_User::find_user($item->data['user_id']);
				if ($link && $user->loaded()) {
					$text = __('tagged :user to a photo', array(':user' => HTML::user($user)));
				}
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

			// Image
			case self::TYPE_COMMENT:
			case self::TYPE_NOTE:
				$gallery = Model_Gallery::factory($item->data['gallery_id']);
				$image   = Model_Image::factory($item->data['image_id']);
				if ($gallery->loaded() && $image->loaded()) {
					$text = HTML::anchor(
						Route::url('gallery_image', array('gallery_id' => Route::model_id($gallery), 'id' => $image->id, 'action' => '')),
						'<i class="icon-camera icon-white"></i> ' . HTML::chars($gallery->name),
						array('class' => 'hoverable')
					);
				}
				break;

			// Flyer
			case self::TYPE_COMMENT_FLYER:
			case self::TYPE_FLYER_EDIT:
				$flyer = Model_Flyer::factory($item->data['flyer_id']);
				if ($flyer->loaded()) {
					$text = HTML::anchor(
						Route::url('flyer', array('id' => $flyer->id)),
						'<i class="icon-picture icon-white"></i> ' . ($flyer->name ? HTML::chars($flyer->name) : __('flyer')),
						array('class' => 'hoverable')
					);
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
	 * @param  Model_Flyer    $flyer
	 * @param  Model_Image    $image
	 */
	public static function comment_flyer(Model_User $user = null, Model_Flyer $flyer = null, Model_Image $image = null) {
		if ($user && $flyer && $image) {
			parent::add($user, 'galleries', self::TYPE_COMMENT_FLYER, array('flyer_id' => (int)$flyer->id, 'image_id' => (int)$image->id));
		}
	}


	/**
	 * Edit a flyer
	 *
	 * @param  Model_User   $user
	 * @param  Model_Flyer  $flyer
	 */
	public static function flyer_edit(Model_User $user = null, Model_Flyer $flyer = null) {
		if ($user && $flyer) {
			parent::add($user, 'galleries', self::TYPE_FLYER_EDIT, array('flyer_id' => (int)$flyer->id));
		}
	}


	/**
	 * Tag a user to an image
	 *
	 * @param  Model_User     $user
	 * @param  Model_Gallery  $gallery
	 * @param  Model_Image    $image
	 * @param  Model_User     $note_user
	 */
	public static function note(Model_User $user = null, Model_Gallery $gallery = null, Model_Image $image = null, Model_User $note_user = null) {
		if ($user && $gallery && $image && $note_user) {
			parent::add($user, 'galleries', self::TYPE_NOTE, array('gallery_id' => (int)$gallery->id, 'image_id' => (int)$image->id, 'user_id' => (int)$note_user->id));
		}
	}


}
