<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Sign up/in/out controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Sign extends Controller_Page {

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
			$user = Model_User::find_user($_POST['username']);

			// Get external account data
			$token = $external_user_id = null;
			if ($provider = Arr::get($_POST, 'external')) {
				$consumer         = new OAuth2_Consumer($provider);
				$token            = $consumer->get_token();
				$external_user_id = Session::instance()->get('oauth2.' . $provider . '.id');
			}
			$success = ($user && $visitor->login($user, $_POST['password'], isset($_POST['remember'])));

			// Log login attempt
			Model_Login::log($success, $user ? $user : $_POST['username'], isset($_POST['password']) && $_POST['password'] != '');

			if (!$success) {

				// Redirect to lost password page on fail
				Request::current()->redirect(Route::url('password'));

			} else if ($token && $external_user_id) {

				// Connect to external account
				$external = Model_User_External::factory()->find_by_user_id($user->id, $provider);

				// Check for already connected account
				if ($external && $external->loaded()) {

					// Already connected, do nuthin'
					Kohana::$log->add(Log::DEBUG, 'OAuth2: Sign in, already connected accounts');

				} else {

					Kohana::$log->add(Log::DEBUG, 'OAuth2: Sign in and connect accounts');

					// Not connected, connect!
					$external = new Model_User_External();
					$external->set_fields(array(
						'token'            => $token['access_token'],
						'user_id'          => $user->id,
						'external_user_id' => $external_user_id,
						'created'          => time(),
						'expires'          => time() + (int)$token['expires'],
						'provider'         => $provider,
					));
					$external->save();

				}

			}
		}

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
	 * Action: Password lost
	 */
	public function action_password() {
		$this->history = false;

		$email = $message = '';

		// Handle request
		if ($_POST && $email = trim(Arr::get($_POST, 'email', ''))) {
			$message = new View_Alert(__('We could not find any user or the user is missing email address, sorry.'), true);

			// Find the user, accept only strings
			$user = Valid::digit($email) ? false : Model_User::find_user(trim($email));

			// Send email
			if ($user && Valid::email($user->email)) {
				$subject = __('Your new :site password', array(':site' => Kohana::$config->load('site.site_name')));
				$mail    = __(
					"Forgot your password, :username?\n\nWe received a request to generate a new password for your :site account, please sign in and change your password. You should also delete this email.\n\nUsername: :username\nPassword: :password",
					array(
						':site'     => Kohana::$config->load('site.site_name'),
						':username' => Text::clean($user->username),
						':password' => Visitor::generate_password($user->password),
					)
				);

				if (Email::send($user->email, Kohana::$config->load('site.email_invitation'), $subject, $mail)) {
					$message = new View_Alert(
						__(':email should soon receive the generated password in their inbox.', array(':email' => $email)),
						true,
						View_Alert::SUCCESS
					);
					$email = '';
				}
			}

		}

		// Build page
		$this->view->title = __('Misplaced your password? Forgot your username?');
		$this->view->add(View_Page::COLUMN_CENTER, $this->section_password($message, $email));
		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_signin());
	}


	/**
	 * Action: sign up
	 */
	public function action_up() {
		$this->history = false;

		if (self::$user) {
			Request::back();
		}

		$this->view->title = __('Join :site', array(':site' => Kohana::$config->load('site.site_name')));

		// Check external provider
		if ($provider = Arr::get($_REQUEST, 'provider')) {
			if ($response = Session::instance()->get('oauth2.' . $provider . '.response')) {
				return $this->_join(null, $response, $provider);
			}
		}

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
				$this->request->redirect(Route::url('sign', array('action' => 'up')) . '?code=' . $code);
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
				$subject = __(':site invite', array(':site' => Kohana::$config->load('site.site_name')));
				$mail    = __(
					"Your invitation code is: :code\n\nOr click directly to sign up: :url",
					array(
						':code' => $invitation->code,
						':url'  => URL::site(Route::get('sign')->uri(array('action' => 'up')) . '?code=' . $invitation->code, true),
					)
				);

				// Send invitation
				if (Email::send($invitation->email, Kohana::$config->load('site.email_invitation'), $subject, $mail)) {
					$invitation->save();

					$message = new View_Alert(__('Invitation sent, should be already in your inbox.'), true, View_Alert::SUCCESS);
				} else {
					$message = new View_Alert(__('Could not send invite to :email', array(':email' => $invitation->email)), true);
				}

			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validation');
			}
		}

		$this->view->add(View_Page::COLUMN_CENTER, $this->section_invitation($invitation, $errors, $message));
	}


	/**
	 * Register with code
	 *
	 * @param  Model_Invitation  $invitation
	 * @param  array             $external
	 * @param  string            $provider
	 */
	public function _join(Model_Invitation $invitation = null, array $external = null, $provider = null) {
		$user = new Model_User();

		if ($invitation) {
			$user->email = $invitation->email;
		} else if ($external) {
			$user->email    = Arr::get($external, 'email');
			$user->name     = Arr::get($external, 'name');
			$user->username = Arr::get($external, 'username', $user->name);
			$user->avatar   = 'https://graph.facebook.com/' . $external['id'] . '/picture';
			$user->picture  = 'https://graph.facebook.com/' . $external['id'] . '/picture?type=large';
			if ($location = Arr::get($external, 'location')) {
				$user->location  = $location->name;
				$user->city_name = $location->name;
			}
			if ($gender = Arr::get($external, 'gender')) {
				switch ($gender) {
					case 'male':   $user->gender = 'm'; break;
					case 'female': $user->gender = 'f'; break;
				}
			}
			if ($birthday = Arr::get($external, 'birthday')) {
				$user->dob = $birthday;
			}
		}

		// Handle post
		$errors = array();
		if ($_POST && !Arr::get($_POST, 'signup')) {
			$post = Arr::extract($_POST, array('username', 'password', 'password_confirm'));
			try {
				$user->username = $post['username'];
				$user->password = $post['password'];
				$user->created  = time();
				$user->save();

				// Delete used invitation
				if ($invitation) {
					$invitation->delete();
				}

				// Connect accounts
				if ($external && $provider) {
					$consumer         = new OAuth2_Consumer($provider);
					$token            = $consumer->get_token();
					$external_user_id = $external['id'];

					if ($token && $external_user_id) {
						$external = new Model_User_External();
						$external->set_fields(array(
							'token'            => $token['access_token'],
							'user_id'          => $user->id,
							'external_user_id' => $external_user_id,
							'created'          => time(),
							'expires'          => time() + (int)$token['expires'],
							'provider'         => $provider,
						));
						$external->save();
					}
				}

				// Login user
				$user->add_role('login');
				Visitor::instance()->login($user, $_POST['password']);

				$this->request->redirect(URL::user($user));
			} catch (Validation_Exception $e) {
				$user->password = $user->password_confirm = null;
				$errors = $e->array->errors('validation');
			}
		}

		$this->view->add(View_Page::COLUMN_CENTER, $this->section_register($user, $errors, $invitation->code));
	}


	/**
	 * Get invitation views.
	 *
	 * @param  Model_Invitation  $invitation
	 * @param  array             $errors
	 * @param  string            $message
	 * @return View_User_Invite
	 */
	public function section_invitation(Model_Invitation $invitation, array $errors = null, $message = null) {
		$section = new View_User_Invite($invitation);
		$section->errors  = $errors;
		$section->message = $message;

		return $section;
	}


	/**
	 * Get lost password view.
	 *
	 * @param   string  $message
	 * @param   string  $email
	 * @return  View_User_Password
	 */
	public function section_password($message = null, $email = null) {
		$section = new View_User_Password();
		$section->message = $message;
		$section->email   = $email;

		return $section;
	}


	/**
	 * Get register view.
	 *
	 * @param   Model_User  $user
	 * @param   array       $errors
	 * @param   string      $code
	 * @return  View_User_Register
	 */
	public function section_register(Model_User $user, array $errors = null, $code = null) {
		$section = new View_User_Register($user, $code);
		$section->errors = $errors;

		return $section;
	}


	/**
	 * Get sign in form.
	 *
	 * @return  View_Index_Signin
	 */
	public function section_signin() {
		$section = new View_Index_Signin();
		$section->aside = true;

		return $section;
	}

}
