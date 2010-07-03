<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Galleries controller
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Galleries extends Controller_Template {

	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		$this->page_title = __('Galleries');
		$this->tabs = array(
			'latest' => array('url' => Route::get('galleries')->uri(), 'text' => __('Latest updates')),
			'browse' => array('url' => Route::get('galleries')->uri(array('action' => 'browse')), 'text' => __('Browse galleries')),
		);
	}


	/**
	 * Action: browse
	 */
	public function action_browse() {
		$this->tab_id = 'browse';

		$months = Model_Gallery::find_months();

		// Default to last month
		$year  = (int)$this->request->param('year');
		$month = (int)$this->request->param('month');
		if (!$year) {
			$year  = max(array_keys($months));
			$month = max(array_keys($months[$year]));
		} else if (!$month) {
			$month = isset($months[$year]) ? min(array_keys($months[$year])) : 1;
		}

		$year  = min($year, date('Y'));
		$month = min(12, max(1, $month));

		$this->page_title .= ' - ' . HTML::chars(date('F Y', mktime(null, null, null, $month, 1, $year)));

		// Month browser
		Widget::add('wide', View_Module::factory('galleries/month_browser', array(
			'year'   => $year,
			'month'  => $month,
			'months' => $months
		)));

		// Galleries
		$galleries = Jelly::select('gallery')->year_month($year, $month)->execute();
		if (count($galleries)) {
			Widget::add('wide', View_Module::factory('galleries/galleries', array(
				'galleries' => $galleries
			)));
		}


	}


	/**
	 * Action: comment
	 */
	public function action_comment() {
		$this->history = false;
		$comment_id = (int)$this->request->param('id');
		$action     = $this->request->param('commentaction');

		// Load blog_comment
		$comment = Jelly::select('image_comment')->load($comment_id);
		if (($action == 'delete' || $action == 'private') && Security::csrf_valid() && $comment->loaded()) {
			$image = $comment->image;
			$gallery = Model_Gallery::find_by_image($image->id);
			switch ($action) {

				// Delete comment
				case 'delete':
			    if (Permission::has($comment, Model_Image_Comment::PERMISSION_DELETE, self::$user)) {
				    $comment->delete();
				    $image->comment_count--;
				    $image->save();
				    $gallery->comment_count--;
				    $gallery->save();
			    }
			    break;

				// Set comment as private
			  case 'private':
				  if (Permission::has($comment, Model_Image_Comment::PERMISSION_UPDATE, self::$user)) {
					  $comment->private = true;
					  $comment->save();
				  }
			    break;

			}
			if (!$this->ajax) {
				$this->request->redirect(Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $image->id, 'action' => '')));
			}
		}

		if (!$this->ajax) {
			Request::back('galleries');
		}
	}


	/**
	 * Action: gallery
	 */
	public function action_gallery() {

		// Load gallery
		$gallery_id = (int)$this->request->param('id');
		$gallery = Jelly::select('gallery')->load($gallery_id);
		if (!$gallery->loaded()) {
			throw new Model_Exception($gallery, $gallery_id);
		}
		Permission::required($gallery, Model_Gallery::PERMISSION_READ, self::$user);

		// Set title and tabs
		$this->_set_gallery($gallery);

		// Pictures
		Widget::add('main', View_Module::factory('galleries/gallery', array(
			'gallery' => $gallery,
		)));

		// Event info
		if ($gallery->event) {
			// Event flyers
			if ($gallery->event->flyer_front->id || $gallery->event->flyer_back->id || $gallery->event->flyer_front_url || $gallery->event->flyer_back_url) {
				Widget::add('side', View_Module::factory('events/flyers', array(
					'event' => $gallery->event,
				)));
			}

			Widget::add('side', View_Module::factory('events/event_info', array(
				'event' => $gallery->event,
				'user'  => self::$user,
			)));
		}
	}


	/**
	 * Action: image
	 */
	public function action_image() {
		$gallery_id = (int)$this->request->param('gallery_id');
		$image_id   = $this->request->param('id');

		// Load gallery
		$gallery = Jelly::select('gallery')->load($gallery_id);
		if (!$gallery->loaded()) {
			throw new Model_Exception($gallery, $gallery_id);
		}

		// Set title and tabs
		$this->_set_gallery($gallery);

		// Find current, previous and next images
		$i = 0;
		$images = $gallery->find_images();
		$previous = $next = $current = null;
		foreach ($images as $image) {
			$i++;
			if (!is_null($current)) {

				// Current was found last round
				$next = $image;
				$i--;
				break;

			} else if ($image->id == $image_id) {

				// Current found now
				$current = $image;

			} else {

				// No current found
				$previous = $image;

			}
		}

		// Show image
		if (!is_null($current)) {

			// Comments section
			if (Permission::has($gallery, Model_Gallery::PERMISSION_COMMENTS, self::$user)) {
				$errors = array();
				$values = array();

				// Handle comment
				if (Permission::has($gallery, Model_Gallery::PERMISSION_COMMENT, self::$user) && $_POST) {
					$comment = Jelly::factory('image_comment');
					$comment->image  = $current;
					if ($current->author) {
						$comment->user   = $current->author;
					}
					$comment->author = self::$user;
					$comment->set(Arr::extract($_POST, Model_Image_Comment::$editable_fields));
					try {
						$comment->save();
						$image->comment_count++;
						$image->save();
						$gallery->comment_count++;
						$gallery->save();

						// Newsfeed
						if (!$comment->private) {
							NewsfeedItem_Galleries::comment(self::$user, $gallery, $current);
						}

						if (!$this->ajax) {
							$this->request->redirect(Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $image->id, 'action' => '')));
						}
					} catch (Validate_Exception $e) {
						$errors = $e->array->errors('validation');
						$values = $comment;
					}

				}

				$comments = $current->comments;
				$view = View_Module::factory('generic/comments', array(
					'mod_title'  => __('Comments'),
					'delete'     => Route::get('gallery_image_comment')->uri(array('id' => '%d', 'commentaction' => 'delete')) . '?token=' . Security::csrf(),
					'private'    => false, //Route::get('gallery_image_comment')->uri(array('id' => '%d', 'commentaction' => 'private')) . '?token=' . Security::csrf(),
					'comments'   => $comments,
					'errors'     => $errors,
					'values'     => $values,
					'pagination' => null,
					'user'       => self::$user,
				));

				if ($this->ajax) {
					echo $view;
					return;
				}
				Widget::add('main', $view);
			}

			// Image
			$current->view_count++;
			$current->save();
			Widget::add('wide', View_Module::factory('galleries/image', array(
				'mod_class' => 'gallery-image',
				'gallery'   => $gallery,
				'images'    => count($images),
				'current'   => $i,
				'image'     => $current,
				'next'      => $next,
				'previous'  => $previous
			)));

			// Image info
			Widget::add('side', View_Module::factory('galleries/image_info', array(
				'mod_title' => __('Picture info'),
				'image'     => $current,
			)));

		}

	}


	/**
	 * Controller default action
	 */
	public function action_index() {
		$this->tab_id = 'latest';

		$galleries = Jelly::select('gallery')->latest()->limit(10)->execute();
		if (count($galleries)) {
			Widget::add('wide', View_Module::factory('galleries/galleries', array(
				'galleries' => $galleries,
			)));
		}
	}


	/**
	 * Set gallery specific title and tabs
	 *
	 * @param  Model_Gallery  $gallery
	 */
	protected function _set_gallery($gallery) {

		// Add new tab
		$this->tab_id = 'gallery';
		$this->tabs['browse']['url'] = Route::get('galleries')->uri(array('action' => 'browse', 'year' => date('Y', $gallery->date), 'month' => date('m', $gallery->date)));
		$this->tabs['gallery'] = array('url' => Route::model($gallery), 'text' => __('Gallery'));

		// Set title
		$this->page_title = HTML::chars($gallery->name);
		$this->page_subtitle = HTML::time(Date::format('DMYYYY', $gallery->date), $gallery->date, true);

	}

}
