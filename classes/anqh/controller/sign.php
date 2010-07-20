<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Sign up/in/out controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
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

			// Log login attempt
			$login = Jelly::factory('login')->set(array(
				'password' => !empty($_POST['password']),
				'username' => $_POST['username'],
				'ip'       => Request::$client_ip,
				'hostname' => Request::host_name()
			));

			// Require valid user for login logging
			$user = Model_User::find_user($_POST['username']);
			if ($user && $user->loaded()) {
				$login->user = $user;
				$login->username = $user->username;

				if ($visitor->login($user, $_POST['password'], isset($_POST['remember']))) {
					$login->success = true;
				}
			}

			$login->save();

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
		Jelly::factory('user_online')->delete(session_id());

		// Logout visitor
		Visitor::instance()->logout();

		// Redirect back
		Request::back();

	}


	/**
	 * Send new invitation
	 *
	 * @param  string  $code  Invalid code given?
	 */
	public function _invite($code = null) {
		$invitation = new Invitation_Model();

		$form_values = $invitation->as_array();
		$form_errors = array();
		$form_message = '';

		if ($code) {

			// Invalid code given
			$form_errors = array('code' => 'default');
			$form_values['code'] = $code;

		} else if (request::method() == 'post') {

	 		// Handle post
			$post = $this->input->post();

			// Validate email
			if ($invitation->validate($post, false)) {

				// Send invitation
				$code = $invitation->code();
				$subject = __(':site invite', array(':site' => Kohana::config('site.site_name')));
				$mail = __("Your invitation code is: :code\n\nOr click directly to sign up: :url", array(':code' => $code, ':url' => url::site('/sign/up/' . $code)));

				// Send invitation
				if (email::send($post->email, Kohana::config('site.email_invitation'), $subject, $mail)) {
					$invitation->code = $code;
					$invitation->save();

					$form_message = __('Invitation sent, you can proceed to Step 2 when you receive your mail.');
				} else {
					$form_message =__('Could not send email to :email', array(':email' => $post->email));
				}

			} else {
				$form_errors = $post->errors();
			}
			$form_values = arr::overwrite($form_values, $post->as_array());
		}

		widget::add('main', View::factory('member/invite', array('values' => $form_values, 'errors' => $form_errors, 'message' => $form_message)));
	}


	/**
	 * Register with code
	 *
	 * @param  Invitation_Model  $invitation
	 */
	public function _join(Invitation_Model $invitation) {
		$user = new User_Model();
		$form_values = $user->as_array();
		$form_errors = array();

		// handle post
		if (request::method() == 'post') {
			$post = $this->input->post();
			$post['email'] = $invitation->email;
			$post['username_clean'] = utf8::clean($post['username']);
			if ($user->validate($post, false, null, null, array('rules' => 'register', 'callbacks' => 'register'))) {
				$invitation->delete();

				$user->add(ORM::factory('role', 'login'));
				$user->save();

				$this->visitor->login($user, $post->password);

				url::back();
			} else {
				$form_errors = $post->errors();
				$form_values = arr::overwrite($form_values, $post->as_array());
			}

		}

		widget::add('main', View::factory('member/signup', array('values' => $form_values, 'errors' => $form_errors, 'invitation' => $invitation)));
	}


	/**
	 * Sign up
	 *
	 * @param  string  $code
	 */
	public function up($code = false) {
		$this->page_title = __('Sign up');

		// Check invitation code
		if ($code) {
			$invitation = new Invitation_Model($code);
			if ($invitation->email) {

				// Valid invitation code found, sign up form
				$this->_join($invitation);

			} else {

				// Invite only hook
				if (Kohana::config('site.inviteonly')) {
					url::redirect('/');
					return;
				}

				$this->_invite($code);
			}
			return;
		}

		// Invite only hook
		if (Kohana::config('site.inviteonly') && !Visitor::instance()->logged_in()) {
			url::redirect('/');
			return;
		}

		// Check if we got the code from the form
		if (!$code && request::method() == 'post') {
			$code = $this->input->post('code');
			if ($code) {
				url::redirect('/sign/up/' . $code);
				return;
			}
		}

		$this->_invite();
	}

}
