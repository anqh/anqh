<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Notification_Galleries
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Notification_Galleries extends Notification {

	const CLASS_GALLERIES = 'galleries';

	/** Image removal request */
	const TYPE_IMAGE_REPORT = 'image_report';


	/**
	 * Get notification as HTML.
	 *
	 * @static
	 * @param   Model_Notification
	 * @return  string
	 */
	public static function get(Model_Notification $notification) {
		$text = '';
		switch ($notification->type) {

			case self::TYPE_IMAGE_REPORT:
				$user  = Model_User::find_user($notification->user_id);
				$image = Model_Image::factory($notification->data_id);
				if ($user->loaded() && $image->loaded()) {
					$gallery = $image->gallery();
					$text   = __(':user reported an :image: <em>:reason</em>', array(
							':user'  => HTML::user($user),
							':image' => HTML::anchor(
									Route::url('gallery_image', array('gallery_id' => Route::model_id($gallery), 'id' => $image->id, 'action' => '')),
									__('image'),
									array('class' => 'hoverable')
								),
							':reason' => $notification->text ? HTML::chars($notification->text) : __('No reason')
					));
				} else {
					$notification->delete();
				}
				break;

		}

		return $text;
	}


	/**
	 * Report image.
	 *
	 * @static
	 * @param  Model_User   $user
	 * @param  Model_Image  $image
	 * @param  string       $reason
	 */
	public static function image_removal_request(Model_User $user, Model_Image $image, $reason = null) {
		if ($user && $image) {
			$author = Model_User::find_user($image->author_id);

			if ($author) {
				parent::add($user, $author, self::CLASS_GALLERIES, self::TYPE_IMAGE_REPORT, $image->id, $reason);
			}
		}
	}

}
