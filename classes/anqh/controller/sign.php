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
		Jelly::factory('user_online')->delete(Session::instance()->id());

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
			$invitation = Model_Invitation::find_by_code($code);

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
		$invitation = Jelly::factory('invitation');

		$errors = array();
		$message = '';
		if ($code) {

			// Invalid code given
			$errors = array('code' => __('Invalid invitation code'));
			$invitation->code = $code;

		} else if ($_POST && !empty($_POST['email'])) {

	 		// Handle post
			$invitation->email = Arr::get($_POST, 'email');
			$invitation->code = $invitation->code();
			try {
				$invitation->validate();

				// Send invitation
				$subject = __(':site invite', array(':site' => Kohana::config('site.site_name')));
				$mail = __(
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

			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validation');
			}
		}

		// Send invitation
		$form = array(
			'values' => $invitation,
			'errors' => $errors,
			'cancel' => Request::back('/', true),
			'save'   => array(
				'label' => __('Send invitation')
			),
			'groups' => array(
				array(
					'html'   => '<p>' . $message . '</p>',
				),
				'invite' => array(
					'header' => __('Not yet invited?'),
					'fields' => array(
						'email' => array(
							'label' => __('Send an invitation to'),
							'tip'   => __('Please remember: sign up is available only with a valid, invited email.'),
							'attributes' => array(
								'title' => __('john.doe@domain.tld'),
							),
						),
					),
				),
			),
		);
		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));

		// Enter invitation
		$form = array(
			'values' => Jelly::factory('invitation'),
			'errors' => $errors,
			'cancel' => Request::back('/', true),
			'save'   => array(
				'label' => __('Final step!')
			),
			'hidden' => array(
				'signup' => true,
			),
			'groups' => array(
				'invited' => array(
					'header' => __('Got my invitation!'),
					'fields' => array(
						'code' => array(
							'label' => __('Enter your invitation code'),
							'tip'   => __('Your invitation code is included in the mail you received, 16 characters.'),
							'attributes' => array(
								'title' => __('M0573XC3LL3N751R'),
							),
						),
					),
				),
			),
		);
		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));

	}


	/**
	 * Register with code
	 *
	 * @param  Model_Invitation  $invitation
	 */
	public function _join(Model_Invitation $invitation) {
		$user = Jelly::factory('user');
		$user->email = $invitation->email;

		// Handle post
		$errors = array();
		if ($_POST && !Arr::get($_POST, 'signup')) {
			$user->set($_POST, array('username', 'password', 'password_confirm'));
			$user->username_clean = Text::clean(Arr::get($_POST, 'username'));
			$user->add('roles', 1);
			try {
				$user->save();
				$invitation->delete();

				Visitor::instance()->login($user, $_POST['password']);

				$this->request->redirect(Route::model($user));
			} catch (Validate_Exception $e) {
				$user->password = $user->password_confirm = null;
				$errors = $e->array->errors('validation');
			}
		}

		// Build form
		$form = array(
			'values' => $user,
			'errors' => $errors,
			'cancel' => Request::back('/', true),
			'save'   => array(
				'label' => __('Sign up!'),
			),
			'hidden' => array(
				'code' => $invitation->code,
			),
			'groups' => array(
				array(
					'header' => __('Almost there!'),
					'fields' => array(

						'username' => array(
							'attributes' => array(
								'title' => __('JohnDoe'),
							),
							'tip' => __(
								'Choose a unique username with at least <var>:length</var> characters. No special characters, thank you.',
								array(':length' => Kohana::config('visitor.username.length_min'))
							),
						),

						'password' => array(),
						'password_confirm' => array(
							'label' => __('Confirm'),
							'tip' => __(
								'Try to use letters, numbers and special characters for a stronger password, with at least <var>8</var> characters.'
							),
						),

						'email' => array(
							'attributes' => array(
								'disabled' => 'disabled',
								'title'    => __('john.doe@domain.tld'),
							),
						)
					),
				),
			)
		);

		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));
	}

}
