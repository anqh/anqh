<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh User controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_User extends Controller_Template {

	/**
	 * Action: comment
	 */
	public function action_comment() {
		$comment_id = (int)$this->request->param('id');
		$action     = $this->request->param('commentaction');

		// Load blog_comment
		$comment = Jelly::select('user_comment')->load($comment_id);
		if (($action == 'delete' || $action == 'private') && Security::csrf_valid() && $comment->loaded()) {
			$user = $comment->user;
			switch ($action) {

				// Delete comment
				case 'delete':
			    if (Permission::has($comment, Model_User_Comment::PERMISSION_DELETE, self::$user)) {
				    $comment->delete();
				    $user->num_comments--;
				    $user->save();
			    }
			    break;

				// Set comment as private
			  case 'private':
				  if (Permission::has($comment, Model_User_Comment::PERMISSION_UPDATE, self::$user)) {
					  $comment->private = true;
					  $comment->save();
				  }
			    break;

			}
			if (!$this->ajax) {
				$this->request->redirect(Route::get('user')->uri(array('username' => urlencode($user->username))));
			}
		}

		if (!$this->ajax) {
			Request::back(Route::get('users')->uri());
		}
	}


	/**
	 * Controller default action
	 */
	public function action_index() {

		// Get our user, default to logged in user if no username given
		$username = urldecode((string)$this->request->param('username'));
		$user = ($username == '') ? self::$user : Model_User::find_user($username);
		if (!$user)	{
			$this->request->redirect(Route::get('users')->uri());
		}

		// Helper variables
		$owner = (self::$user && self::$user->id == $user->id);

		$this->page_title = HTML::chars($user->username);
		if ($user->title) {
			$this->page_subtitle = HTML::chars($user->title);
		}

		// Portrait
		Widget::add('side', View_Module::factory('user/image', array(
			'user' => $user,
		)));

		// Comments section
		if (Permission::has($user, Model_User::PERMISSION_COMMENTS, self::$user)) {
			$errors = array();
			$values = array();

			// Handle comment
			if (Permission::has($user, Model_User::PERMISSION_COMMENT, self::$user) && $_POST) {
				$comment = Jelly::factory('user_comment');
				$comment->user       = $user;
				$comment->author     = self::$user;
				$comment->set(Arr::extract($_POST, Model_User_Comment::$editable_fields));
				try {
					$comment->save();

					// Receiver
					$user->num_comments++;
					if (!$owner) {
						$user->new_comments++;
					}
					$user->save();

					// Sender
					self::$user->num_comments_left++;
					self::$user->save();

					// Newsfeed
					if (!$comment->private) {
						//NewsfeedItem_Blog::comment(self::$user, $entry);
					}

					if (!$this->ajax) {
						$this->request->redirect(Route::get('user')->uri(array('username' => urlencode($user->username))));
					}
				} catch (Validate_Exception $e) {
					$errors = $e->array->errors('validation');
					$values = $comment;
				}

			}

			// Pagination
			$per_page = 25;
			$pagination = Pagination::factory(array(
				'items_per_page' => $per_page,
				'total_items'    => max(1, $user->get('comments')->viewer(self::$user)->count()),
			));

			$view = View_Module::factory('generic/comments', array(
				'delete'     => Route::get('user_comment')->uri(array('id' => '%d', 'commentaction' => 'delete')) . '?token=' . Security::csrf(),
				'private'    => Route::get('user_comment')->uri(array('id' => '%d', 'commentaction' => 'private')) . '?token=' . Security::csrf(),
				'comments'   => $user->get('comments')->viewer(self::$user)->pagination($pagination)->execute(),
				'errors'     => $errors,
				'values'     => $values,
				'pagination' => $pagination,
				'user'       => self::$user,
			));

			if ($this->ajax) {
				echo $view;
				return;
			}
			Widget::add('main', $view);
		}
	}


	/**
	 * Action: peep
	 */
	public function action_hover() {

		// Hover card works only with ajax
		if (!$this->ajax) {
			return $this->action_index();
		}

		$user = Model_User::find_user(urldecode((string)$this->request->param('username')));
		if ($user)	{
			echo View_Module::factory('user/hovercard', array(
				'mod_title' => $user->username,
				'user'      => $user
			));
		}
	}

}
