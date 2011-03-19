<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * NewsfeedItem Blog
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_NewsfeedItem_Blog extends NewsfeedItem {

	/**
	 * Comment an entry
	 *
	 * Data: entry_id
	 */
	const TYPE_COMMENT = 'comment';

	/**
	 * Write a new entry
	 *
	 * Data: entry_id
	 */
	const TYPE_ENTRY = 'entry';


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
				$entry = Model_Blog_Entry::factory($item->data['entry_id']);
				if ($entry->loaded()) {
					$text = __('commented to blog<br />:blog', array(
						':blog' => HTML::anchor(
							Route::model($entry),
							HTML::chars($entry->name),
							array('title' => $entry->name, 'class' => 'icon blog')
						)
					));
				}
				break;

			case self::TYPE_ENTRY:
				$entry = Model_Blog_Entry::factory($item->data['entry_id']);
				if ($entry->loaded()) {
					$text = __('wrote a new blog entry<br />:blog', array(
						':blog' => HTML::anchor(
							Route::model($entry),
							HTML::chars($entry->name),
							array('title' => $entry->name, 'class' => 'icon blog')
						)
					));
				}
				break;

		}

		return $text;
	}


	/**
	 * Write a new entry
	 *
	 * @param  Model_User        $user
	 * @param  Model_Blog_Entry  $entry
	 */
	public static function entry(Model_User $user = null, Model_Blog_Entry $entry = null) {
		if ($user && $entry) {
			parent::add($user, 'blog', self::TYPE_ENTRY, array('entry_id' => (int)$entry->id));
		}
	}


	/**
	 * Comment an entry
	 *
	 * @param  Model_User        $user
	 * @param  Model_Blog_Entry  $entry
	 */
	public static function comment(Model_User $user = null, Model_Blog_Entry $entry = null) {
		if ($user && $entry) {
			parent::add($user, 'blog', self::TYPE_COMMENT, array('entry_id' => (int)$entry->id));
		}
	}

}
