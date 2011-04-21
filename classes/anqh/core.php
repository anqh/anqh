<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Core {

	/**
	 * Anqh version
	 *
	 * Roadmap:
	 * 0.6 = Kohana 3.1.1
	 * 0.7 = AutoModeler
	 * 0.8 = DB Schemas
	 * 0.9 = Kostache(?)
	 * 1.0 = Optimizations
	 */
	const VERSION = 0.8;

	/**
	 * @var  array  Static local cache in front of external cache
	 */
	private static $_cache = array();

	/**
	 * @var  Cache  Cache instance for default cache
	 */
	private static $_cache_instance;


	/**
	 * Delete a cache entry based on id
	 *
	 * @param   string  $id  id to remove from cache
	 * @return  boolean
	 */
	public static function cache_delete($id) {
		!self::$_cache_instance and self::$_cache_instance = Cache::instance();

		unset(self::$_cache[$id]);

		return self::$_cache_instance->delete_($id);
	}


	/**
	 * Retrieve a cached value entry by id.
	 *
	 * @param   string  $id       id of cache to entry
	 * @param   string  $default  default value to return if cache miss
	 * @return  mixed
	 * @throws  Kohana_Cache_Exception
	 */
	public static function cache_get($id, $default = null) {
		!self::$_cache_instance and self::$_cache_instance = Cache::instance();

		if (!isset(self::$_cache[$id])) {
			self::$_cache[$id] = self::$_cache_instance->get_($id, $default);
		}

		return Arr::get(self::$_cache, $id, $default);
	}


	/**
	 * Set a value to cache with id and lifetime
	 *
	 * @param   string   $id        id of cache entry
	 * @param   string   $data      data to set to cache
	 * @param   integer  $lifetime  in seconds
	 * @return  boolean
	 */
	public static function cache_set($id, $data, $lifetime = 3600) {
		!self::$_cache_instance and self::$_cache_instance = Cache::instance();

		if (self::$_cache_instance->set_($id, $data, $lifetime)) {
			self::$_cache[$id] = $data;

		  return true;
		}

		return false;
	}


	/**
	 * Get user's new comment counts
	 *
	 * @return  array
	 */
	public static function notifications(Model_User $user) {
		$new = array();

		// Profile comments
		if ($user->new_comment_count) {
			$new['new-comments'] = HTML::anchor(URL::user($user), $user->new_comment_count, array('title' => __('New comments')));
		}

		// Forum private messages
		$private_messages = Forum::find_new_private_messages($user);
		if (count($private_messages)) {
			$new_messages = 0;
			foreach ($private_messages as $private_message) {
				$new_messages += $private_message->unread;
			}
			$new['new-private-messages'] = HTML::anchor(Route::model($private_message->topic()) . '?page=last#last', $new_messages, array('title' => __('New private messages')));
		}
		unset($private_messages);

		// Blog comments
		$blog_comments = Model_Blog_Entry::factory()->find_new_comments($user);
		if (count($blog_comments)) {
			$new_comments = 0;
			foreach ($blog_comments as $blog_entry) {
				$new_comments += $blog_entry->new_comment_count;
			}
			$new['new-blog-comments'] = HTML::anchor(Route::model($blog_entry), $new_comments, array('title' => __('New blog comments')));
		}
		unset($blog_comments);

		// Forum quotes
		$forum_quotes = Model_Forum_Quote::factory()->find_by_user($user);
		if (count($forum_quotes)) {
			$new_quotes = count($forum_quotes);
			$quote = $forum_quotes->current();
			$new['new-forum-quotes'] = HTML::anchor(
				Route::get('forum_post')->uri(array('topic_id' => $quote->forum_topic_id, 'id' => $quote->forum_post_id)) . '#post-' . $quote->forum_post_id,
				$new_quotes,
				array('title' => __('Forum quotes')
			));
		}

		// Images waiting for approval
		if (Permission::has(new Model_Gallery, Model_Gallery::PERMISSION_APPROVE_WAITING, $user)) {
			$gallery_approvals = Model_Gallery::factory()->find_pending(Permission::has(new Model_Gallery, Model_Gallery::PERMISSION_APPROVE, $user) ? null : $user);
			if (count($gallery_approvals)) {
				$new_approvals = count($gallery_approvals);
				$new['new-gallery-approvals'] = HTML::anchor(
					Route::get('galleries')->uri(array('action' => 'approval')),
					$new_approvals,
					array('title' => __('Galleries waiting for approval')
				));
			}
		}

		// Flyer comments
		$flyer_comments = Model_Flyer::factory()->find_new_comments($user);
		$flyers = array();
		if (count($flyer_comments)) {
			$new_comments = 0;
			foreach ($flyer_comments as $flyer) {
				$flyers[$flyer->image_id] = true;
				$new_comments += $flyer->image()->new_comment_count;
			}
			$new['new-flyer-comments'] = HTML::anchor(
				Route::get('flyer')->uri(array('id' => $flyer->id, 'action' => '')),
				$new_comments,
				array('title' => __('New flyer comments')
			));
		}
		unset($flyer_comments);

		// Image comments
		$image_comments = Model_Image::factory()->find_new_comments($user);
		$note_comments  = Model_Image_Note::factory()->find_new_comments($user);
		if (count($image_comments) || count($note_comments)) {
			$new_comments = 0;
			$new_image = null;
			foreach ($image_comments as $image) {

				// @TODO: Until flyer comments are fixed..
				if (!isset($flyers[$image->id])) {
					$new_comments += $image->new_comment_count;
					$new_image_id = $image->id;
				}

			}
			foreach ($note_comments as $note) {
				$new_comments += $note->new_comment_count;
				$new_image_id = $note->image_id;
			}

			if ($new_comments) {
				$new['new-image-comments'] = HTML::anchor(
					Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id(Model_Gallery::find_by_image($new_image_id)), 'id' => $new_image_id, 'action' => '')),
					$new_comments,
					array('title' => __('New image comments')
				));
			}
		}
		unset($image_comments, $note_comments, $new_image);

		// Image tags
		$notes  = Model_Image_Note::factory()->find_new_notes($user);
		if (count($notes)) {
			$new_notes = 0;
			$new_note_image_id = null;

			/** @var  Model_Image_Note  $note */
			foreach ($notes as $note) {
				$new_notes++;
				$new_note_image_id = $note->image_id;
			}

			if ($new_notes) {
				$new['new-image-notes'] = HTML::anchor(
					Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id(Model_Gallery::find_by_image($new_note_image_id)), 'id' => $new_note_image_id, 'action' => '')),
					$new_notes,
					array('title' => __('New image tags')
				));
			}
		}
		unset($note_comments, $new_note_image_id);

		return $new;

	}
	/**
	 * Get/set Open Graph tags
	 *
	 * @static
	 * @param   string  $key    Null to get all
	 * @param   string  $value  Null to get, false to clear
	 * @return  mixed
	 */
	public static function open_graph($key = null, $value = null) {
		static $og;

		// Initialize required Open Graph tags when setting first value
	  if ($value && !is_array($og)) {
		  if ($app_id = Kohana::config('site.facebook')) {
				$og = array(
					'og:title'     => Kohana::config('site.site_name'),
					'og:type'      => 'article',
					'og:image'     => URL::site('/ui/opengraph.jpg', true),
					'og:url'       => URL::site('', true),
					'og:site_name' => Kohana::config('site.site_name'),
					'fb:app_id'    => $app_id,
				);
		  }
	  }

	  if (!is_array($og)) {

		  // Facebook/Open Graph disabled
		  return;


	  } else if (is_null($value)) {

		  // Get
		  return is_null($key) ? $og : Arr::get($og, 'og:' . $key);

	  } else if ($value === false) {

		  // Delete
		  unset($og['og:' . $key]);

	  } else {

		  // Set
		  $og['og:' . $key] = $value;

	  }
	}


	/**
	 * Get/set shareability
	 *
	 * @static
	 * @param   boolean  $shareable  boolean to set, null to get
	 * @return  boolean
	 */
	public static function share($shareable = null) {
		static $share;

	  if (is_bool($shareable)) {

		  // Set shareability
		  $share = $shareable;

	  } else {

		  // Get shareability
		  return (bool)$share;

	  }
	}

}
