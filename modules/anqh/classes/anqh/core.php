<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Core {

	/** Anqh version */
	const VERSION = '1.0';

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
	 * Get user's new comment counts.
	 *
	 * @param   Model_User  $user
	 * @return  array
	 */
	public static function notifications(Model_User $user) {
		$new = array();

		// Profile comments
		if ($user->new_comment_count) {
			$new['new-comments'] = HTML::anchor(
				URL::user($user),
				'<i class="fa fa-comment"></i> ' . $user->new_comment_count,
				array('class' => '', 'title' => __('New comments'))
			);
		}

		// Forum private messages
		$private_messages = Forum::find_new_private_messages($user);
		if (count($private_messages)) {
			$new_messages = 0;
			foreach ($private_messages as $private_message) {
				$new_messages += $private_message->unread;
			}
			$new['new-private-messages'] = HTML::anchor(
				Route::model($private_message->topic()) . '?page=last#last',
				'<i class="fa fa-comment"></i> ' . $new_messages,
				array('class' => '', 'title' => __('New private messages'))
			);
		}
		unset($private_messages);

		// Blog comments
		$blog_comments = Model_Blog_Entry::factory()->find_new_comments($user);
		if (count($blog_comments)) {
			$new_comments = 0;
			foreach ($blog_comments as $blog_entry) {
				$new_comments += $blog_entry->new_comment_count;
			}
			$new['new-blog-comments'] = HTML::anchor(
				Route::model($blog_entry),
				'<i class="fa fa-comment"></i> ' . $new_comments,
				array('class' => '', 'title' => __('New blog comments'))
			);
		}
		unset($blog_comments);

		// Forum quotes
		$forum_quotes = Model_Forum_Quote::factory()->find_by_user($user);
		if (count($forum_quotes)) {
			$new_quotes = count($forum_quotes);
			$quote = $forum_quotes->current();
			$new['new-forum-quotes'] = HTML::anchor(
				Route::get('forum_post')->uri(array('topic_id' => $quote->forum_topic_id, 'id' => $quote->forum_post_id)) . '#post-' . $quote->forum_post_id,
				'<i class="fa fa-comment"></i> ' . $new_quotes,
				array('class' => '', 'title' => __('Forum quotes'))
			);
		}

		/** @Deprecated */
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
				'<i class="fa fa-picture"></i> ' . $new_comments,
				array('class' => '', 'title' => __('New flyer comments'))
			);
		}
		unset($flyer_comments);

		/** @Deprecated */
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
					'<i class="fa fa-camera-retro"></i> ' . $new_comments,
					array('class' => '', 'title' => __('New image comments'))
				);
			}
		}
		unset($image_comments, $note_comments, $new_image);

		/** @Deprecated */
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
					'<i class="fa fa-tag"></i> ' . $new_notes,
					array('class' => '', 'title' => __('New image tags'))
				);
			}
		}
		unset($note_comments, $new_note_image_id);

		// Notification system
		$notifications = Notification::get_notifications($user);
		if (count($notifications)) {
			$new['new-notifications'] = HTML::anchor(
				Route::url('notifications'),
				'<i class="fa fa-bell"></i> <span class="badge">' . count($notifications) . '</span>',
				array('class' => 'notifications', 'title' => __('New notifications'), 'onclick' => 'return false;')
			);
		}

		return $new;
	}


	/**
	 * Get/set page meta data tags, Open Graph and Twitter Card
	 *
	 * @static
	 * @param   string  $key    Null to get all
	 * @param   string  $value  Null to get, false to clear
	 * @return  mixed
	 */
	public static function page_meta($key = null, $value = null) {
		static $meta    = array();
		static $fb      = array();
		static $twitter = array();

		static $opengraph_tags = array(
			'author'      => 'article:author',
			'description' => 'og:description',
			'image'       => 'og:image',
			'site'        => 'og:site_name',
			'summary'     => 'article:section',
			'title'       => 'og:title',
			'type'        => 'og:type',
			'url'         => 'og:url',
		);

		// Initialize required meta data when setting first value
	  if ($value && empty($meta)) {
		  $config = Kohana::$config->load('site.og');

			$meta = array(
				'title' => Kohana::$config->load('site.site_name'),
				'type'  => 'article',
//				'url'   => URL::site('', true),
				'site'  => Kohana::$config->load('site.site_name'),
			);
		  if ($config['image']) {
			  $meta['image'] = URL::site($config['image'], true);
		  }

		  // Facebook
		  if ($app_id = Kohana::$config->load('site.facebook')) {
			  $fb['fb:app_id'] = $app_id;
		  }

		  // Twitter
		  $twitter['twitter:card'] = 'summary';
		  if ($site = Kohana::$config->load('site.twitter_username')) {
			  $twitter['twitter:site'] = $site;
		  }
		  if ($site_id = Kohana::$config->load('site.twitter_id')) {
			  $twitter['twitter:site:id'] = $site_id;
		  }

	  }

		if (strpos($key, 'fb:') === 0) {
			$data = &$fb;
		} else if (strpos($key, 'twitter:') === 0) {
			$data = &$twitter;
		} else {
			$data = &$meta;
		}

		if (is_null($value)) {
			if (is_null($key)) {

				// Get all
				$combined = $fb + $twitter;
				foreach ($meta as $_key => $_value) {
					if (isset($opengraph_tags[$_key])) {
						$combined[$opengraph_tags[$_key]] = $_value;
					} else {
						$combined[$_key] = $_value;
					}
				}

				return $combined;

			}	else {

				// Get one key
				return $data[$key];

			}

	  } else if ($value === false) {

			// Delete
			unset($data[$key]);

		} else {

			// Set
			$data[$key] = $value;

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
