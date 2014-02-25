<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Forum {

	/**
	 * Forum version
	 */
	const VERSION = 0.5;


	/**
	 * Find new private messages
	 *
	 * @static
	 * @param   Model_User $user
	 * @return  Model_Forum_Private_Recipient[]
	 */
	public static function find_new_private_messages(Model_User $user) {
		$recipient = Model_Forum_Private_Recipient::factory();

		return $recipient->load(
			DB::select_array($recipient->fields())
				->where('user_id', '=', $user->id)
				->where('unread', '>', 0),
			null
		);
	}


	/**
	 * Get private messages URL
	 *
	 * @static
	 * @return  string
	 */
	public static function private_messages_url() {
		return Route::get('forum_private_area')->uri();
	}


	/**
	 * Get prefixed forum title.
	 *
	 * @static
	 * @param   Model_Forum_Topic  $topic
	 * @return  string
	 */
	public static function topic(Model_Forum_Topic $topic) {
		$prefix = array();

		// Private topic
		if ($topic->recipient_count) {
			$prefix[] = $topic->recipient_count > 2
				? '<i class="fa fa-group text-muted" title="' . __(':recipients recipients', array(':recipients' => Num::format($topic->recipient_count, 0))) . '"></i>'
				: '<i class="fa fa-envelope text-muted" title="' .  __('Personal message') . '"></i>';
		}

		// Stickyness
		if ($topic->sticky) {
			$prefix[] = '<i class="fa fa-thumb-tack text-warning" title="' . __('Pinned') . '"></i>';
		}

		// Status
		switch ($topic->status) {
			case Model_Forum_Topic::STATUS_LOCKED: $prefix[] = '<i class="fa fa-lock text-muted" title="' . __('Locked') . '"></i>'; break;
			case Model_Forum_Topic::STATUS_SINK:   $prefix[] = '<i class="fa fa-unlock text-muted" title="' . __('Sink') . '"></i>'; break;
		}

		return implode(' ', $prefix) . ' ' . HTML::chars($topic->name);
	}

}
