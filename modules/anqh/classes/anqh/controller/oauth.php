<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh OAuth controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_OAuth extends Controller_Page {

	/**
	 * @var  OAuth2_Consumer
	 */
	public $consumer;

	/**
	 * @var  Model_User_External
	 */
	public $external;


	/**
	 * Construct controller.
	 *
	 * @throws  OAuth2_Exception_UnsupportedGrantType
	 */
	public function before() {
		parent::before();

		$this->history = false;

		// See if we already have a token
		$provider = $this->request->param('provider');
		if (self::$user && $this->external = Model_User_External::factory()->find_by_user_id(self::$user->id, $provider)) {

			// Access token should be available
			$this->consumer = new OAuth2_Consumer($provider, $this->external->loaded() ? $this->external->access_token() : false);

		} else {

			// No access token available
			$this->consumer = new OAuth2_Consumer($provider, false);

		}
	}


	/**
	 * Action: Login with 3rd party credentials.
	 */
	public function action_disconnect() {
		if ($this->external && $this->external->loaded()) {
			$this->external->delete();

			$this->request->redirect(URL::user(self::$user, 'settings'));
		}

		Request::back();
	}


	/**
	 * Controller default action
	 */
	public function action_index() {

	}


	/**
	 * Action: Login with 3rd party credentials.
	 */
	public function action_login() {
		if ($token = $this->consumer->get_token()) {
			if (self::$user || $this->_login($token)) {

				// Already logged in
				Request::back();

			}

			// Login failed, continue with requesting new token

		}

		// No access token available, request authorization code to be changed to access token
		$grant = $this->consumer->get_grant_type();
		$this->request->redirect($grant->get_redirect_uri(null, OAuth2::RESPONSE_TYPE_CODE));

	}


	/**
	 * Action: Redirected from 3rd party.
	 */
	public function action_redirect() {
		$provider = $this->consumer->get_provider();
		if ($provider != 'facebook') {

			// Unsupported provider
			$this->view->add(View_Page::COLUMN_MAIN, new View_Alert(
					__('We are not entirely sure what 3rd party service redirected you here'),
					__('Failed to load your profile :('),
					View_Alert::ERROR));

			Kohana::$log->add(Log::NOTICE, 'OAuth2: Unsupported provider: :provider', array(':provider' => $provider));
			return;

		}

		if ($response = Arr::get($_REQUEST, OAuth2::RESPONSE_TYPE_CODE)) {

			// Code received, change it to access token
			try {
				$token = $this->consumer->request_token(array(OAuth2::RESPONSE_TYPE_CODE => $response));

				if (self::$user) {

					// Already logged in
					$external = Model_User_External::factory()->find_by_user_id(self::$user->id, $provider);
					if ($this->_update_token($external, $token)) {

						// Already paired with local user
						$this->request->redirect(URL::user(self::$user, 'settings'));
						//Request::back();

					} else {

						// Not paired with local user, do so
						if ($response = $this->consumer->api_call('/me', array('fields' => 'id,email'))) {

							// Received a response from 3rd party
							if ($error = Arr::get($response, 'error')) {

								// .. but it was an error
								$this->view->add(View_Page::COLUMN_MAIN, new View_Alert(
										__('They said ":error"', array(':error' => HTML::chars($error->message))),
										__('Failed to load your profile :('),
										View_Alert::ERROR));

								Kohana::$log->add(Log::NOTICE, 'OAuth2: Failed to load Facebook profile: :error', array(':error' => $error->message));

							} else {

								// Received required information
								$external = new Model_User_External();
								$external->set_fields(array(
									'token'            => $token['access_token'],
									'user_id'          => self::$user->id,
									'external_user_id' => Arr::get($response, 'id'),
									'created'          => time(),
									'expires'          => time() + (int)$token['expires'],
									'provider'         => $provider,
								));
								$external->save();

								$this->request->redirect(URL::user(self::$user, 'settings'));
								//Request::back();

							}

						} else {

							// No data received, this should be handled by exceptions

						}

					}

				} else {

					// No signed in user available
					if ($response = $this->consumer->api_call('/me')) {

						// Received a response from 3rd party
						if ($error = Arr::get($response, 'error')) {

							// .. but it was an error
							$this->view->add(View_Page::COLUMN_MAIN, new View_Alert(
									__('They said ":error"', array(':error' => HTML::chars($error->message))),
									__('Failed to load your profile :('),
									View_Alert::ERROR));

							Kohana::$log->add(Log::NOTICE, 'OAuth2: Failed to load Facebook profile: :error', array(':error' => $error->message));

						} else {

							// Received required information
							$external_user_id = Arr::get($response, 'id');
							$external         = Model_User_External::factory()->find_by_external_user_id($external_user_id, $provider);
							if ($this->_update_token($external, $token)) {

								// Already paired with local user, login
								Kohana::$log->add(Log::DEBUG, 'OAuth2: Attempting to login :external_user_id => :user_id', array(':external_user_id' => $external->external_user_id, ':user_id' => $external->user_id));
								if ($this->_login($external)) {
									Request::back();
								}
								Kohana::$log->add(Log::WARNING, 'OAuth2: Login failed');

							} else {

								// Not paired with a local user, check if we have unpaired user available
								$email = Arr::get($response, 'email');

								// Store external user id in session data, token should be stored in OAuth2
								Session::instance()->set('oauth2.' . $provider . '.id', $external_user_id);
								if ($user = Model_User::find_user($email)) {

									// User with same email found, ask to sign in
									Kohana::$log->add(Log::DEBUG, 'OAuth2: Existing user with same email found');
									$this->view->add(View_Page::COLUMN_MAIN, $this->section_signin($user, $response));

								} else {

									// No user with same email found, start registering
									Kohana::$log->add(Log::DEBUG, 'OAuth2: Starting new user registration');

									Session::instance()->set('oauth2.' . $provider . '.response', $response);
									$this->request->redirect(Route::url('sign', array('action' => 'up')) . '?provider=' . $provider);
								}


							}
						}

					} else {

						// No data received, this should be handled by exceptions

					}

				}

			} catch (OAuth2_Exception_InvalidGrant $e) {

				$this->view->add(View_Page::COLUMN_MAIN, new View_Alert(
						HTML::chars($e->getMessage()),
						__('Failed to load your profile :('),
						View_Alert::ERROR));

				Kohana::$log->add(Log::NOTICE, 'OAuth2: Invalid grant: :error', array(':error' => $e->getMessage()));


			} catch (Kohana_Exception $e) {

				$this->view->add(View_Page::COLUMN_MAIN, new View_Alert(
						HTML::chars($e->getMessage()),
						__('Failed to load your profile :('),
						View_Alert::ERROR));

				Kohana::$log->add(Log::NOTICE, 'OAuth2: Exception: :error', array(':error' => $e->getMessage()));

			}

		} else {

			$this->view->add(View_Page::COLUMN_MAIN, new View_Alert(
					__('Did not receive required code from 3rd party'),
					__('Failed to load your profile :('),
					View_Alert::ERROR));

			Kohana::$log->add(Log::NOTICE, 'OAuth2: No code received');

		}
	}


	/**
	 * Try to login external user.
	 *
	 * @param   Model_User_External  $external
	 * @param   string               $token
	 * @return  boolean
	 */
	protected function _login(Model_User_External $external = null, $token = null) {
		if (!$external) {
			$external = new Model_User_External($token);
		}

		if ($external->loaded() && $external->provider === $this->consumer->get_provider()) {
			return Visitor::instance()->external_login($external);
		}

		return false;
	}


	/**
	 * Get password view.
	 *
	 * @param   Model_User  $user
	 * @param   array       $external_user
	 * @return  View_User_OAuth
	 */
	public function section_signin(Model_User $user, array $external_user) {
		return new View_User_OAuth($user, $external_user);
	}


	/**
	 * Update old token.
	 *
	 * @param   Model_User_External  $external
	 * @param   string               $token
	 * @return  boolean              If old matches provider
	 */
	protected function _update_token(Model_User_External &$external, $token) {
		if ($external->loaded() && $external->provider == $this->consumer->get_provider()) {
			$external->token    = $token['access_token'];
			$external->modified = time();
			$external->expires  = time() + (int)$token['expires'];
			$external->save();

			return true;
		}

		return false;
	}

}
