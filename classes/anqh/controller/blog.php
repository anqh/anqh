<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Blog controller
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Blog extends Controller_Template {

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
		$comment = Jelly::select('blog_comment')->load($comment_id);
		if (($action == 'delete' || $action == 'private') && Security::csrf_valid() && $comment->loaded()) {
			$entry = $comment->blog_entry;
			switch ($action) {

				// Delete comment
				case 'delete':
			    if (Permission::has($comment, Model_Blog_Comment::PERMISSION_DELETE, $this->user)) {
				    $comment->delete();
				    $entry->num_comments--;
				    $entry->save();
			    }
			    break;

				// Set comment as private
			  case 'private':
				  if (Permission::has($comment, Model_Blog_Comment::PERMISSION_UPDATE, $this->user)) {
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
		$entry = Jelly::select('blog_entry')->load($entry_id);
		if (!$entry->loaded()) {
			throw new Model_Exception($entry, $entry_id);
		}
		Permission::required($entry, Model_Blog_Entry::PERMISSION_READ, $this->user);

		// Set title
		$this->page_title    = HTML::chars($entry->name);
		$this->page_subtitle = __('By :user :ago', array(
			':user'  => HTML::user($entry->author),
			':ago'   => HTML::time(Date::fuzzy_span($entry->created), $entry->created)
		));

		// Set actions
		if (Permission::has(new Model_Blog_Entry, Model_Blog_Entry::PERMISSION_UPDATE, $this->user)) {
			$this->page_actions[] = array('link' => Route::model($entry, 'edit'), 'text' => __('Edit blog entry'), 'class' => 'blog-edit');
		}

		Widget::add('main', View_Module::factory('blog/entry', array(
			'entry' => $entry
		)));

		// Comments section
		if (Permission::has($entry, Model_Blog_Entry::PERMISSION_COMMENTS, $this->user)) {
			$errors = array();
			$values = array();

			// Handle comment
			if (Permission::has($entry, Model_Blog_Entry::PERMISSION_COMMENT, $this->user) && $_POST) {
				$comment = Jelly::factory('blog_comment');
				$comment->blog_entry = $entry;
				$comment->user       = $entry->author;
				$comment->author     = $this->user;
				$comment->set(Arr::extract($_POST, Model_Blog_Comment::$editable_fields));
				try {
					$comment->save();
					$entry->num_comments++;
					$entry->new_comments++;
					$entry->save();

					if (!$this->ajax) {
						$this->request->redirect(Route::model($entry));
					}
				} catch (Validate_Exception $e) {
					$errors = $e->array->errors('validation');
					$values = $comment;
				}

			}

			$comments = $entry->comments;
			$view = View_Module::factory('generic/comments', array(
				'delete'     => Route::get('blog_comment')->uri(array('id' => '%d', 'commentaction' => 'delete')) . '?token=' . Security::csrf(),
				'private'    => Route::get('blog_comment')->uri(array('id' => '%d', 'commentaction' => 'private')) . '?token=' . Security::csrf(),
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


	/**
	 * Controller default action
	 */
	public function action_index() {
		$this->page_title = __('Blogs');

		// Set actions
		if (Permission::has(new Model_Blog_Entry, Model_Blog_Entry::PERMISSION_CREATE, $this->user)) {
			$this->page_actions[] = array('link' => Route::get('blogs')->uri(array('action' => 'add')), 'text' => __('Add blog entry'), 'class' => 'blog-add');
		}

		Widget::add('main', View_Module::factory('blog/entries', array(
			'mod_class' => 'blog_entries',
			'entries'   => Jelly::select('blog_entry')->limit(20)->execute(),
		)));
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
			$entry = Jelly::select('blog_entry')->load($entry_id);
			if (!$entry->loaded()) {
				throw new Model_Exception($entry, $entry_id);
			}
			Permission::required($entry, Model_Blog_Entry::PERMISSION_UPDATE, $this->user);
			$cancel = Route::model($entry);

		} else {

			// Creating new
			$entry = Jelly::factory('blog_entry');
			Permission::required($entry, Model_Blog_Entry::PERMISSION_CREATE, $this->user);
			$cancel = Request::back(Route::get('blogs')->uri(), true);

			$entry->author = $this->user;

		}

		// Handle post
		$errors = array();
		if ($_POST) {
			$entry->set($_POST);
			try {
				$entry->save();
				$this->request->redirect(Route::model($entry));
			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validation');
			}
		}

		// Build form
		$form = array(
			'values' => $entry,
			'errors' => $errors,
			'cancel' => $cancel,
			'groups' => array(
				array(
					'fields' => array(
						'name'  => array(),
						'entry' => array(),
					),
				),
			)
		);

		Widget::add('head', HTML::script('js/jquery.markitup.pack.js'));
		Widget::add('head', HTML::script('js/markitup.bbcode.js'));
		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));
	}

}
