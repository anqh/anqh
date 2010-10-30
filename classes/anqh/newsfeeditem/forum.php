<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum NewsfeedItem
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
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
	 * Get newsfeed item as HTML
	 *
	 * @static
	 * @param   Model_NewsfeedItem  $item
	 * @return  string
	 */
	public static function get(Model_NewsFeedItem $item) {
		$text = '';

		switch ($item->type) {

			case self::TYPE_REPLY:
				$topic = Jelly::select('forum_topic')->load($item->data['topic_id']);
				if ($topic->loaded()) {
					$text = __('replied to topic :topic', array(
						':topic' => HTML::anchor(
							Route::get('forum_post')->uri(array('topic_id' => Route::model_id($topic), 'id' => $item->data['post_id'])) . '#post-' . $item->data['post_id'],
							HTML::chars($topic->name), array('title' => $topic->name))
						)
					);
				}
				break;

			case self::TYPE_TOPIC:
				$topic = Jelly::select('forum_topic')->load($item->data['topic_id']);
				if ($topic->loaded()) {
					$text = __('started a new topic :topic', array(':topic' => HTML::anchor(Route::model($topic), HTML::chars($topic->name), array('title' => $topic->name))));
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
			parent::add($user, 'forum', self::TYPE_REPLY, array('topic_id' => (int)$post->topic->id, 'post_id' => (int)$post->id));
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
