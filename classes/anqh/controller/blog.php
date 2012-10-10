<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Blog controller
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Blog extends Controller_Page {

	/**
	 * Action: add new blog entry
	 */
	public function action_add() {
		return $this->_edit_entry();
	}


	/**
	 * Action: comment
	 */
	public function action_comment() {
		$comment_id = (int)$this->request->param('id');
		$action     = $this->request->param('commentaction');

		// Load blog_comment
		$comment = Model_Blog_Comment::factory($comment_id);
		if (($action == 'delete' || $action == 'private') && Security::csrf_valid() && $comment->loaded()) {
			$entry = $comment->blog_entry();
			switch ($action) {

				// Delete comment
				case 'delete':
			    if (Permission::has($comment, Model_Blog_Comment::PERMISSION_DELETE, self::$user)) {
				    $comment->delete();
				    $entry->comment_count--;
				    $entry->save();
			    }
			    break;

				// Set comment as private
			  case 'private':
				  if (Permission::has($comment, Model_Blog_Comment::PERMISSION_UPDATE, self::$user)) {
					  $comment->private = true;
					  $comment->save();
				  }
			    break;

			}
			if (!$this->ajax) {
				$this->request->redirect(Route::model($entry));
			}
		}

		if (!$this->ajax) {
			Request::back('blogs');
		}
	}


	/**
	 * Action: edit blog entry
	 */
	public function action_edit() {
		return $this->_edit_entry((int)$this->request->param('id'));
	}


	/**
	 * Action: blog entry
	 */
	public function action_entry() {
		$entry_id = (int)$this->request->param('id');

		// Load blog entry
		$entry = Model_Blog_Entry::factory($entry_id);
		if (!$entry->loaded()) {
			throw new Model_Exception($entry, $entry_id);
		}
		Permission::required($entry, Model_Blog_Entry::PERMISSION_READ, self::$user);


		// Comments section
		if (Permission::has($entry, Model_Blog_Entry::PERMISSION_COMMENTS, self::$user)) {
			$errors = array();
			$values = array();

			if ($_POST && Permission::has($entry, Model_Blog_Entry::PERMISSION_COMMENT, self::$user)) {

				// Handle comment
				try {
					$comment = Model_Blog_Comment::factory()->
						add(self::$user->id, $entry, Arr::get($_POST, 'comment'), Arr::get($_POST, 'private'));

					$entry->comment_count++;
					$entry->new_comment_count++;
					$entry->save();

					// Newsfeed
					if (!$comment->private) {
						NewsfeedItem_Blog::comment(self::$user, $entry);
					}

					if ($this->_request_type !== Controller::REQUEST_AJAX) {
						$this->request->redirect(Route::model($entry));
					}
				} catch (Validation_Exception $e) {
					$errors = $e->array->errors('validation');
					$values = $comment;
				}

			}

			// Get view
			$section_comments = $this->section_comments($entry);
			$section_comments->errors = $errors;
			$section_comments->values = $values;

		} else if (!self::$user) {

			// Guest user
			$section_comments = $this->section_comments_teaser($entry->comment_count);

		}

		if (isset($section_comments) && $this->_request_type === Controller::REQUEST_AJAX) {
			$this->response->body($section_comments);

			return;
		}


		// Build page
		$this->view           = new View_Page($entry->name);
		$this->view->subtitle = __('By :user :ago', array(
			':user'  => HTML::user($entry->author()),
			':ago'   => HTML::time(Date::fuzzy_span($entry->created), $entry->created)
		));

		// Set actions
		if (Permission::has($entry, Model_Blog_Entry::PERMISSION_UPDATE, self::$user)) {
			$this->page_actions[] = array(
				'link'  => Route::model($entry, 'edit'),
				'text'  => '<i class="icon-edit icon-white"></i> ' . __('Edit blog entry'),
			);
		}

		// Content
		$this->view->add(View_Page::COLUMN_MAIN, $this->section_entry($entry));

		// Comments
		if (isset($section_comments)) {
			$this->view->add(View_Page::COLUMN_MAIN, $section_comments);
		}

		// Update counts
		if (self::$user && self::$user->id == $entry->author_id) {

			// Clear new comment counts for owner
			if ($entry->new_comment_count) {
				$entry->new_comment_count = 0;
				$entry->save();
			}

		} else {
			$entry->view_count++;
			$entry->save();
		}

	}


	/**
	 * Controller default action
	 */
	public function action_index() {

		$this->view = new View_Page(__('Blogs'));

		// Set actions
		if (Permission::has(new Model_Blog_Entry, Model_Blog_Entry::PERMISSION_CREATE, self::$user)) {
			$this->page_actions[] = array(
				'link'  => Route::url('blogs', array('action' => 'add')),
				'text'  => '<i class="icon-plus-sign icon-white"></i> ' . __('Write new blog entry'),
				'class' => 'btn btn-primary'
			);
		}


		$this->view->add(View_Page::COLUMN_MAIN, $this->section_blogs(Model_Blog_Entry::factory()->find_new(20)));
	}


	/**
	 * Edit entry
	 *
	 * @param  integer  $entry_id
	 */
	protected function _edit_entry($entry_id = null) {
		$this->history = false;

		if ($entry_id) {

			// Editing old
			$entry = new Model_Blog_Entry($entry_id);
			if (!$entry->loaded()) {
				throw new Model_Exception($entry, $entry_id);
			}
			Permission::required($entry, Model_Blog_Entry::PERMISSION_UPDATE, self::$user);

			$cancel = Route::model($entry);

			$this->view = new View_Page(HTML::chars($entry->name));
			$entry->modified  = time();
			$entry->modify_count++;

		} else {

			// Creating new
			$entry = new Model_Blog_Entry();
			Permission::required($entry, Model_Blog_Entry::PERMISSION_CREATE, self::$user);

			$cancel   = Request::back(Route::get('blogs')->uri(), true);
			$newsfeed = true;

			$this->view = new View_Page(__('New blog entry'));
			$entry->author_id = self::$user->id;
			$entry->created   = time();

		}

		// Handle post
		$errors = array();
		if ($_POST && Security::csrf_valid()) {
			try {
				$entry->name    = Arr::get($_POST, 'name');
				$entry->content = Arr::get($_POST, 'content');
				$entry->save();

				// Newsfeed
				if (isset($newsfeed) && $newsfeed) {
					NewsfeedItem_Blog::entry(self::$user, $entry);
				}

				$this->request->redirect(Route::model($entry));
			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validation');
			}
		}

		// Form
		$section = $this->section_entry_edit($entry);
		$section->cancel = $cancel;
		$section->errors = $errors;
		$this->view->add(View_Page::COLUMN_MAIN, $section);
	}


	/**
	 * Get blog entries index listing.
	 *
	 * @param   Model_Blog_Entry[]  $blog_entries
	 * @return  View_Blogs_Index
	 */
	public function section_blogs($blog_entries) {
		return new View_Blogs_Index($blog_entries);
	}


	/**
	 * Get blog entry view.
	 *
	 * @param   Model_Blog_Entry  $blog_entry
	 * @return  View_Blog_Entry
	 */
	public function section_entry(Model_Blog_Entry $blog_entry) {
		return new View_Blog_Entry($blog_entry);
	}


	/**
	 * Get blog entry edit view.
	 *
	 * @param   Model_Blog_Entry  $blog_entry
	 * @return  View_Blog_Edit
	 */
	public function section_entry_edit(Model_Blog_Entry $blog_entry) {
		return new View_Blog_Edit($blog_entry);
	}


	/**
	 * Get comments section.
	 *
	 * @param   Model_Blog_Entry  $blog_entry
	 * @param   string            $route
	 * @return  View_Generic_Comments
	 */
	public function section_comments(Model_Blog_Entry $blog_entry, $route = 'blog_comment') {
		$section = new View_Generic_Comments($blog_entry->comments(self::$user));
		$section->delete  = Route::url($route, array('id' => '%d', 'commentaction' => 'delete')) . '?token=' . Security::csrf();
		$section->private = Route::url($route, array('id' => '%d', 'commentaction' => 'private')) . '?token=' . Security::csrf();

		return $section;
	}


	/**
	 * Get comment section teaser.
	 *
	 * @param   integer  $comment_count
	 * @return  View_Generic_CommentsTeaser
	 */
	public function section_comments_teaser($comment_count = 0) {
		return new View_Generic_CommentsTeaser($comment_count);
	}

}
