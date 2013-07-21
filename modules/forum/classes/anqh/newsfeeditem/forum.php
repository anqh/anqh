<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum NewsfeedItem
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_NewsfeedItem_Forum extends NewsfeedItem {

	/**
	 * Reply to a topic
	 *
	 * Data: topic_id, post_id
	 */
	const TYPE_REPLY = 'reply';

	/**
	 * Start a new topic
	 *
	 * Data: topic_id
	 */
	const TYPE_TOPIC = 'topic';

	/**
	 * @var  array  Aggregate types
	 */
	public static $aggregate = array(self::TYPE_REPLY, self::TYPE_TOPIC);


	/**
	 * Get newsfeed item as HTML
	 *
	 * @static
	 * @param   Model_NewsfeedItem  $item
	 * @return  string
	 */
	public static function get(Model_NewsFeedItem $item) {
		$link = $item->is_aggregate() ? implode('<br />', self::get_links($item)) : self::get_link($item);
		if (!$link) {
			return '';
		}

		$text = '';
		switch ($item->type) {

			case self::TYPE_REPLY:
				$text = $item->is_aggregate() ? __('replied to topics') : __('replied to a topic');
				break;

			case self::TYPE_TOPIC:
				$text = $item->is_aggregate() ? __('started new topics') : __('started a new topic');
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

			case self::TYPE_REPLY:
				$topic = Model_Forum_Topic::factory($item->data['topic_id']);
				if ($topic->loaded()) {
					$text = HTML::anchor(
						Route::get('forum_post')->uri(array('topic_id' => Route::model_id($topic), 'id' => $item->data['post_id'])) . '#post-' . $item->data['post_id'],
						'<i class="icon-comments"></i> ' . HTML::chars($topic->name),
						array('title' => $topic->name)
					);
				}
				break;

			case self::TYPE_TOPIC:
				$topic = Model_Forum_Topic::factory($item->data['topic_id']);
				if ($topic->loaded()) {
					$text = HTML::anchor(
						Route::model($topic),
						'<i class="icon-comments"></i> ' . HTML::chars($topic->name),
						array('title' => $topic->name)
					);
				}
				break;

		}

		return $text;
	}


	/**
	 * Reply to a topic
	 *
	 * @static
	 * @param  Model_User        $user
	 * @param  Model_Forum_Post  $post
	 */
	public static function reply(Model_User $user = null, Model_Forum_Post $post = null) {
		if ($user && $post) {
			parent::add($user, 'forum', self::TYPE_REPLY, array('topic_id' => (int)$post->forum_topic_id, 'post_id' => (int)$post->id));
		}
	}


	/**
	 * Start a new topic
	 *
	 * @static
	 * @param  Model_User         $user
	 * @param  Model_Forum_Topic  $topic
	 */
	public static function topic(Model_User $user = null, Model_Forum_Topic $topic = null) {
		if ($user && $topic) {
			parent::add($user, 'forum', self::TYPE_TOPIC, array('topic_id' => (int)$topic->id));
		}
	}

}
