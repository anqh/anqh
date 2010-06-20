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
			'latest' => array('link' => Route::get('galleries')->uri(), 'text' => __('Latest updates')),
			'browse' => array('link' => Route::get('galleries')->uri(array('action' => 'browse')), 'text' => __('Browse galleries')),
		);
	}


	/**
	 * Action: comment
	 */
	public function action_comment() {
		$comment_id = (int)$this->request->param('id');
		$action     = $this->request->param('commentaction');

		// Load blog_comment
		$comment = Jelly::select('image_comment')->load($comment_id);
		if (($action == 'delete' || $action == 'private') && Security::csrf_valid() && $comment->loaded()) {
			$image = $comment->image;
			switch ($action) {

				// Delete comment
				case 'delete':
			    if (Permission::has($comment, Model_Image_Comment::PERMISSION_DELETE, $this->user)) {
				    $comment->delete();
				    $image->num_comments--;
				    $image->save();
			    }
			    break;

				// Set comment as private
			  case 'private':
				  if (Permission::has($comment, Model_Image_Comment::PERMISSION_UPDATE, $this->user)) {
					  $comment->private = true;
					  $comment->save();
				  }
			    break;

			}
			if (!$this->ajax) {
				$gallery = Model_Gallery::find_by_image($image->id);
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
		Permission::required($gallery, Model_Gallery::PERMISSION_READ, $this->user);

		// Set title and tabs
		$this->_set_gallery($gallery);

		// Pictures
		Widget::add('main', View_Module::factory('galleries/gallery', array(
			'gallery' => $gallery,
		)));

		// Event info
		if ($gallery->event) {
			Widget::add('side', View_Module::factory('events/event_info', array(
				'event' => $gallery->event,
				'user'  => $this->user,
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
			$i++;
		}

		// Show image
		if (!is_null($current)) {

			// Image
			$current->num_views++;
			$current->save();
			Widget::add('wide', View_Module::factory('galleries/image', array(
				'gallery'  => $gallery,
				'images'   => count($images),
				'current'  => $i,
				'image'    => $current,
				'next'     => $next,
				'previous' => $previous
			)));

			// Comments section
			if (Permission::has($gallery, Model_Gallery::PERMISSION_COMMENTS, $this->user)) {
				$errors = array();
				$values = array();

				// Handle comment
				if (Permission::has($gallery, Model_Gallery::PERMISSION_COMMENT, $this->user) && $_POST) {
					$comment = Jelly::factory('image_comment');
					$comment->image  = $current;
					if ($current->author) {
						$comment->user   = $current->author;
					}
					$comment->author = $this->user;
					$comment->set(Arr::extract($_POST, Model_Image_Comment::$editable_fields));
					try {
						$comment->save();
						$image->num_comments++;
						$image->save();

						// Newsfeed
						if (!$comment->private) {
							NewsfeedItem_Galleries::comment($this->user, $gallery, $current);
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
					'delete'     => Route::get('gallery_image_comment')->uri(array('id' => '%d', 'commentaction' => 'delete')) . '?token=' . Security::csrf(),
					'private'    => Route::get('gallery_image_comment')->uri(array('id' => '%d', 'commentaction' => 'private')) . '?token=' . Security::csrf(),
					'comments'   => $comments,
					'errors'     => $errors,
					'values'     => $values,
					'pagination' => null,
					'user'       => $this->user,
				));

				if ($this->ajax) {
					echo $view;
					return;
				}
				Widget::add('main', $view);
			}

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
		$this->tabs['browse']['link'] = Route::get('galleries')->uri(array('action' => 'browse', 'year' => date('Y', $gallery->event_date), 'month' => date('m', $gallery->event_date)));
		$this->tabs['gallery'] = array('link' => Route::model($gallery), 'text' => __('Gallery'));

		// Set title
		$this->page_title = HTML::chars($gallery->name);
		$this->page_subtitle = HTML::time(Date::format('DMYYYY', $gallery->event_date), $gallery->event_date, true);

	}

}
