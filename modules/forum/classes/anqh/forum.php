<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
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

		if ($topic->sticky) {
			$prefix[] = '<i class="icon-pushpin text-warning" title="' . __('Pinned') . '"></i>';
		}

		switch ($topic->status) {
			case Model_Forum_Topic::STATUS_LOCKED: $prefix[] = '<i class="icon-lock muted" title="' . __('Locked') . '"></i>'; break;
			case Model_Forum_Topic::STATUS_SINK:   $prefix[] = '<i class="icon-unlock muted" title="' . __('Sink') . '"></i>'; break;
		}

		return implode(' ', $prefix) . ' ' . HTML::chars($topic->name);
	}

}
