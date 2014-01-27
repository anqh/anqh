<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Blog controller
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Blog extends Controller_Page {
	public $page_id = 'blogs';


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
						add(self::$user->id, null, Arr::get($_POST, 'comment'), Arr::get($_POST, 'private'), $entry);

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

		// Set actions
		if (Permission::has($entry, Model_Blog_Entry::PERMISSION_UPDATE, self::$user)) {
			$this->view->actions[] = array(
				'link'  => Route::model($entry, 'edit'),
				'text'  => '<i class="fa fa-edit"></i> ' . __('Edit blog entry'),
			);
		}

		// Content
		$this->view->add(View_Page::COLUMN_CENTER, $this->section_entry($entry, true));

		// Comments
		if (isset($section_comments)) {
			$this->view->add(View_Page::COLUMN_CENTER, $section_comments);
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

		// Month browser
		$author = Model_User::find_user($entry->author_id);
		if ($months = $this->_build_months(Model_Blog_Entry::factory()->find_by_user($author))) {
			$params = array('username' => urlencode($author->username));
			$this->view->add(View_Page::COLUMN_RIGHT, $this->section_month_browser($months, 'blog_user', $params, date('Y', $entry->created), date('n', $entry->created)));
		}

	}


	/**
	 * Controller default action
	 */
	public function action_index() {
		$this->view = new View_Page(__('Blogs'));

		// Set actions
		if (Permission::has(new Model_Blog_Entry, Model_Blog_Entry::PERMISSION_CREATE, self::$user)) {
			$this->view->actions[] = array(
				'link'  => Route::url('blogs', array('action' => 'add')),
				'text'  => '<i class="fa fa-pencil"></i> ' . __('Write new blog entry'),
				'class' => 'btn btn-primary'
			);
		}

		foreach (Model_Blog_Entry::factory()->find_new(20) as $entry) {
			$this->view->add(View_Page::COLUMN_CENTER, $this->section_entry($entry, true));
		}
	}


	/**
	 * Action: user's blog
	 */
	public function action_user() {
		$user = Model_User::find_user(urldecode((string)$this->request->param('username')));
		if (!$user) {
			$this->request->redirect(Route::url('blogs'));

			return;
		}

		$blogs = Model_Blog_Entry::factory()->find_by_user($user);
		if ($months = $this->_build_months($blogs)) {

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

			// Build page
			$this->view        = Controller_User::_set_page($user);
			$this->view->tab   = 'blog';
			$this->view->add(View_Page::COLUMN_CENTER, '<h2>' . HTML::chars(date('F Y', mktime(null, null, null, $month, 1, $year))) . '</h2>');

			// Pagination
			$params = array('username' => urlencode($user->username));
			$this->view->add(View_Page::COLUMN_CENTER, $this->section_month_pagination($months, 'blog_user', $params, $year, $month));

			// Entries
			if (isset($months[$year]) && isset($months[$year][$month])) {
				foreach ($months[$year][$month] as $entry) {
					$this->view->add(View_Page::COLUMN_CENTER, $this->section_entry($entry, true));
				}
			}

			// Month browser
			$this->view->add(View_Page::COLUMN_RIGHT, $this->section_month_browser($months, 'blog_user', $params, $year, $month));

		} else {

			// No entires found
			$this->view->add(View_Page::COLUMN_CENTER, new View_Alert(__('Alas, the quill seems to be dry, no blog entries found.'), null, View_Alert::INFO));

		}
	}


	/**
	 * Build month browser compatible months.
	 *
	 * @param   Model_Blog_Entry[]  $blog_entries
	 * @return  array
	 */
	protected function _build_months($blog_entries = null) {
		$months = array();

		if ($blog_entries && count($blog_entries)) {
			foreach ($blog_entries as $entry) {
				list($year, $month) = explode(' ', date('Y n', $entry->created));

				if (!isset($months[$year])) {
					$months[$year] = array();
				}
				if (!isset($months[$year][$month])) {
					$months[$year][$month] = array();
				}

				$months[$year][$month][] = $entry;
			}

			// Sort years
			krsort($months);
			foreach ($months as &$year) {
				krsort($year);
			}

		}

		return $months;
	}


	/**
	 * Edit entry
	 *
	 * @param   integer  $entry_id
	 *
	 * @throws  Model_Exception
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

			$this->view->title = __('Edit blog entry');
			$entry->modified   = time();
			$entry->modify_count++;

		} else {

			// Creating new
			$entry = new Model_Blog_Entry();
			Permission::required($entry, Model_Blog_Entry::PERMISSION_CREATE, self::$user);

			$cancel   = Request::back(Route::get('blogs')->uri(), true);
			$newsfeed = true;

			$this->view->title = __('New blog entry');
			$entry->author_id  = self::$user->id;
			$entry->created    = time();

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
		$this->view->add(View_Page::COLUMN_CENTER, $section);
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


	/**
	 * Get blog entry view.
	 *
	 * @param   Model_Blog_Entry  $blog_entry
	 * @param   boolean           $show_title
	 * @return  View_Blog_Entry
	 */
	public function section_entry(Model_Blog_Entry $blog_entry, $show_title = false) {
		return new View_Blog_Entry($blog_entry, $show_title);
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
	 * Get months browser.
	 *
	 * @param   array    $months
	 * @param   string   $route
	 * @param   array    $params
	 * @param   integer  $year
	 * @param   integer  $month
	 * @return  View_Generic_Months
	 */
	public function section_month_browser(array $months, $route = 'blog_user', array $params = null, $year = null, $month = null) {
		$section = new View_Generic_Months($months, $route, $params);
		$section->aside = true;
		$section->year  = $year;
		$section->month = $month;

		return $section;
	}


	/**
	 * Get pagination.
	 *
	 * @param   array    $months
	 * @param   string   $route
	 * @param   array    $params
	 * @param   integer  $year
	 * @param   integer  $month
	 * @return  View_Generic_Pagination
	 */
	public function section_month_pagination(array $months, $route, array $params = null, $year, $month) {

		// Previous
		$all_years  = array_keys($months);
		$all_year   = array_search($year, $all_years);
		$all_months = array_keys($months[$year]);
		$all_month  = array_search($month, $all_months);
		if ($all_month < count($all_months) - 1) {
			$previous_year  = $year;
			$previous_month = $all_months[$all_month + 1];
		} else if ($all_year < count($all_years) - 1) {
			$previous_year  = $all_years[$all_year + 1];
			$previous_month = array_keys($months[$previous_year]);
			$previous_month = $previous_month[count($previous_month) - 1];
		} else {
			$previous_year  = $previous_month = null;
		}

		// Next
		if ($all_month > 0) {
			$next_year  = $year;
			$next_month = $all_months[$all_month - 1];
		} else if ($all_year > 0) {
			$next_year  = $all_years[$all_year - 1];
			$_months    = array_keys($months[$next_year]);
			$next_month = reset($_months);
		} else {
			$next_year  = $next_month = null;
		}

		return new View_Generic_Pagination(array(
			'previous_text' => '&laquo; ' . __('Previous month'),
			'next_text'     => __('Next month') . ' &raquo;',
			'previous_url'  => $previous_month
				? Route::url($route, array_merge((array)$params, array(
						'year'   => $previous_year,
						'month'  => $previous_month,
					)))
				: false,
			'next_url'      => $next_month
				? Route::url($route, array_merge((array)$params, array(
						'year'   => $next_year,
						'month'  => $next_month,
					)))
				: false,
		));
	}

}
