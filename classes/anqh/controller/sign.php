<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Sign up/in/out controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Sign extends Controller_Template {

	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		$this->history = false;
	}


	/**
	 * Sign in
	 */
	public function action_in() {
		if (self::$user) {
			Request::back();
		}

		if ($_POST) {
			$visitor = Visitor::instance();

			// Require valid user for login logging
			$user    = Model_User::find_user($_POST['username']);
			$success = ($user && $visitor->login($user, $_POST['password'], isset($_POST['remember'])));

			// Log login attempt
			Model_Login::log($success, $user ? $user : $_POST['username'], isset($_POST['password']) && $_POST['password'] != '');

		} else {

			// 3rd party sign in
			/*
			if (FB::enabled()) {
				$this->visitor->external_login(User_External_Model::PROVIDER_FACEBOOK);
			}
			*/

		}

		// Add newsfeed item
		// newsfeeditem_user::login($this->visitor->get_user());

		Request::back();
	}


	/**
	 * Action: sign out
	 */
	public function action_out() {

		// Remove from online list
		Model_User_Online::factory(Session::instance()->id())->delete();

		// Logout visitor
		Visitor::instance()->logout();

		Request::back();
	}


	/**
	 * Action: sign up
	 */
	public function action_up() {
		$this->history = false;

		if (self::$user) {
			Request::back();
		}

		$this->page_title = __('Sign up');

		// Check invitation code
		$code = trim(Arr::get($_REQUEST, 'code'));
		if ($code) {
			$invitation = Model_Invitation::factory($code);

			return $invitation->loaded() ? $this->_join($invitation) : $this->_invite($code);
		}

		// Check if we got the code from the form
		if (!$code && $_POST) {
			$code = Arr::get($_POST, 'code');
			if ($code) {
				$this->request->redirect(Route::get('sign')->uri(array('action' => 'up')) . '?code=' . $code);
			}
		}

		$this->_invite();
	}


	/**
	 * Send new invitation
	 *
	 * @param  string  $code  Invalid code given?
	 */
	protected function _invite($code = null) {

		/** @var  Model_Invitation  $invitation */
		$invitation = Model_Invitation::factory();

		$errors = array();
		$message = '';
		if ($code) {

			// Invalid code given
			$errors = array('code' => __('Invalid invitation code'));
			$invitation->code = $code;

		} else if ($_POST && !empty($_POST['email'])) {

	 		// Handle post
			$invitation->email = Arr::get($_POST, 'email');
			$invitation->code  = $invitation->code();
			try {
				$invitation->is_valid();

				// Send invitation
				$subject = __(':site invite', array(':site' => Kohana::config('site.site_name')));
				$mail    = __(
					"Your invitation code is: :code\n\nOr click directly to sign up: :url",
					array(
						':code' => $invitation->code,
						':url'  => URL::site(Route::get('sign')->uri(array('action' => 'up')) . '?code=' . $invitation->code, true),
					)
				);

				// Send invitation
				if (Email::send($invitation->email, Kohana::config('site.email_invitation'), $subject, $mail)) {
					$invitation->save();

					$message = '<p>' . __('Invitation sent, you can proceed to Step 2 when you receive your mail.') . '<p>';
				} else {
					$message = '<p>' . __('Could not send invite to :email', array(':email' => $invitation->email)) . '<p>';
				}

			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validation');
			}
		}

		// Send invitation
		Widget::add('main', View_Module::factory('generic/invite', array(
			'invitation' => $invitation,
			'errors'     => $errors,
			'message'    => $message
		)));

		// Enter invitation
		Widget::add('main', View_Module::factory('generic/invited', array(
			'errors'     => $errors,
		)));

	}


	/**
	 * Register with code
	 *
	 * @param  Model_Invitation  $invitation
	 */
	public function _join(Model_Invitation $invitation) {
		$user = new Model_User();
		$user->email = $invitation->email;

		// Handle post
		$errors = array();
		if ($_POST && !Arr::get($_POST, 'signup')) {
			$post = Arr::extract($_POST, array('username', 'password', 'password_confirm'));
			$validation = new Validation($post);
			$validation->rule('password_confirm', 'matches', array(':validation', 'password', 'password_confirm'));
			try {
				$user->username = $post['username'];
				$user->password = $post['password'];
				$user->created  = time();
				$user->save($validation);

				// Delete used invitation
				$invitation->delete();

				// Login user
				$user->add_role('login');
				Visitor::instance()->login($user, $_POST['password']);

				$this->request->redirect(URL::user($user));
			} catch (Validation_Exception $e) {
				$user->password = $user->password_confirm = null;
				$errors = $e->array->errors('validation');
			}
		}

		// Build form
		Widget::add('main', View_Module::factory('generic/register', array(
			'user'   => $user,
			'code'   => $invitation->code,
			'errors' => $errors,
		)));
	}

}
