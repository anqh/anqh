<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Site visitor library with roles for Anqh, handles authorization.
 * Based heavily on Auth Library by Kohana Team.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Visitor {

	/**
	 * @var  array  Configuration values
	 */
	protected $_config;

	/**
	 * @var  Visitor  Instance
	 */
	protected static $_instance;

	/**
	 * @var  Session  Instance
	 */
	protected $_session;

	/**
	 * @var  Model_User|boolean  Current logged in user
	 */
	public static $user = false;


	/**
	 * Loads Session and configuration options.
	 *
	 * @param  array  $config
	 */
	public function __construct($config = array()) {
		$config['salt_pattern'] = Arr::get($config, 'salt_pattern', Kohana::$config->load('visitor')->get('salt_pattern'));
		if (!is_array($config['salt_pattern'])) {
			$config['salt_pattern'] = preg_split('/,\s*/', $config['salt_pattern']);
		}

		$this->_config  = $config;
		$this->_session = Session::instance();
	}


	/**
	 * Attempt to automatically log a user in.
	 *
	 * @return  Model_User
	 */
	public function auto_login() {
		if ($token = Cookie::get($this->_config['cookie_name'])) {

			// Load the token and user
			$token = new Model_User_Token($token);
			if ($token->loaded() && $token->user_id) {
				$user = Model_User::find_user($token->user_id);
				if ($user && $token->user_agent === sha1(Request::$user_agent)) {

					// Save the token to create a new unique token
					$token->update();

					// Set the new token
					Cookie::set($this->_config['cookie_name'], $token->token, $token->expires - time());

					// Complete the login with the found data
					$this->complete_login($user, true);

					// Automatic login was successful
					return $user;
				}

				// Token is invalid
				$token->delete();
			}
		}

		return null;
	}


	/**
	 * Compare password with hashed original
	 *
	 * @param   string  $password
	 * @return  boolean
	 */
	public function check_password($password) {
		$user = $this->get_user();
		if (!$user) {
			return false;
		}

		$hash = $this->hash_password($password, $this->find_salt($user->password_kohana));

		return $hash == $user->password_kohana;
	}


	/**
	 * Complete the login for a user.
	 *
	 * @param   Model_User  $user
	 * @param   boolean     $autologin
	 * @return  boolean
	 */
	protected function complete_login(Model_User $user, $autologin = false) {
		self::$user = $user;

		$user->complete_login($autologin);

		// Regenerate session_id and store user id
		$this->_session->regenerate();
		$this->_session->set($this->_config['session_key'], $user->id);

		return true;
	}


	/**
	 * Attempt to login with 3rd party account
	 *
	 * @param   Model_User_External  $external
	 * @return  boolean
	 */
	public function external_login(Model_User_External $external) {
		if ($external->loaded() && $user = new Model_User($external->user_id)) {
			return $user->loaded() && $this->complete_login($user);
		}

		return false;
	}


	/**
	 * Create an instance of Visitor.
	 *
	 * @param   array  $config
	 * @return  Visitor
	 */
	public static function factory($config = array()) {
		return new Visitor($config);
	}


	/**
	 * Finds the salt from a password, based on the configured salt pattern.
	 *
	 * @param   string  $password  hashed
	 * @return  string
	 */
	public function find_salt($password) {
		$salt = '';

		// Find salt characters, take a good long look...
		foreach ($this->_config['salt_pattern'] as $i => $offset) {
			$salt .= substr($password, $offset + $i, 1);
		}

		return $salt;
	}


	/**
	 * Force a login for a specific username.
	 *
	 * @param   Model_User  $user
	 * @return  boolean
	 */
	public function force_login(Model_User $user) {

		// Mark the session as forced, to prevent users from changing account information
		$_SESSION['visitor_forced'] = true;

		// Run the standard completion
		$this->complete_login($user);
	}


	/**
	 * Generate a "temporary" password from original password to be used with password retrieval mail.
	 *
	 * @param   string  $password
	 * @return  string
	 */
	public static function generate_password($password) {
		return md5($password);
	}


	/**
	 * Get config parameter(s) of Visitor
	 *
	 * @param   string  $key
	 * @param   mixed   $default
	 * @return  array|mixed
	 */
	public function get_config($key = null, $default = null) {
		return $key ? Arr::path($this->_config, $key, $default) : $this->_config;
	}


	/**
	 * Get 3rd party provider used to sign in
	 *
	 * @return  string
	 */
	public function get_provider() {
		return $this->_session->get($this->_config['session_key'] . '_provider', null);
	}


	/**
	 * Gets the currently logged in user from the session or null.
	 *
	 * @return  Model_User
	 */
	public function get_user() {
		if (self::$user === false) {
			if ($user_id = $this->_session->get($this->_config['session_key'], null)) {
				self::$user = Model_User::find_user($user_id);
			} else {
				self::$user = null;
			}
		}

		return self::$user;
	}


	/**
	 * Creates a hashed password from a plaintext password, inserting salt
	 * based on the configured salt pattern.
	 *
	 * @param   string           $password  plaintext
	 * @param   string|boolean   $salt
	 * @return  string           hashed password
	 */
	public function hash_password($password, $salt = false) {

		// Create a salt seed, same length as the number of offsets in the pattern
		if ($salt === false) {
			$salt = substr(hash($this->_config['hash_method'], uniqid(null, true)), 0, count($this->_config['salt_pattern']));
		}

		// Password hash that the salt will be inserted into
		$hash = hash($this->_config['hash_method'], $salt . $password);

		// Change salt to an array
		$salt = str_split($salt, 1);

		// Returned password
		$password = '';

		// Used to calculate the length of splits
		$last_offset = 0;

		foreach ($this->_config['salt_pattern'] as $offset) {

			// Split a new part of the hash off
			$part = substr($hash, 0, $offset - $last_offset);

			// Cut the current part out of the hash
			$hash = substr($hash, $offset - $last_offset);

			// Add the part to the password, appending the salt character
			$password .= $part . array_shift($salt);

			// Set the last offset to the current offset
			$last_offset = $offset;

		}

		// Return the password, with the remaining hash appended
		return $password . $hash;
	}


	/**
	 * Return a static instance of Visitor.
	 *
	 * @return  Visitor
	 */
	public static function instance() {
		if (!self::$_instance) {
			self::$_instance = new Visitor(Kohana::$config->load('visitor'));
		}

		return self::$_instance;
	}


	/**
	 * Checks if a session is active.
	 *
	 * @param   string|array  $roles  OR matched
	 * @return  boolean
	 */
	public function logged_in($roles = null) {
		$status = false;

		// Get the user from the session
		$user = $this->get_user();

		// Not logged in, maybe autologin?
		if (!is_object($user) && $this->_config['lifetime']) {
			$user = $this->auto_login();
		}

		// Check if potential user has optional roles
		if ($user instanceof Model_User && $user->loaded()) {
			$status = empty($roles) || $user->has_role($roles);
		}

		return $status;
	}


	/**
	 * Attempt to log in a user by using an ORM object and plain-text password.
	 *
	 * @param   Model_User  $user
	 * @param   string      $password  plain text
	 * @param   boolean     $remember  auto-login
	 * @return  boolean
	 */
	public function login(Model_User $user, $password, $remember = false) {
		if (!$password || !$user) {
			return false;
		}

		// Get the salt from the stored password
		$salt = $this->find_salt($user->password_kohana);

		// Create a hashed password using the salt from the stored password
		$hashed_password = $this->hash_password($password, $salt);

		// If the passwords match to hashed password or "generated" password, perform a login
		if (($user->password_kohana === $hashed_password || self::generate_password($user->password_kohana) === $password) && $user->has_role('login')) {
			if ($remember === true) {

				// Create a new autologin token
				$token = new Model_User_Token();
				$token->user_id = $user->id;
				$token->expires = time() + $this->_config['lifetime'];
				$token->create();

				// Set the autologin cookie
				Cookie::set($this->_config['cookie_name'], $token->token, $this->_config['lifetime']);
			}

			// Finish the login
			$this->complete_login($user);

			return true;
		}

		// Login failed
		return false;
	}


	/**
	 * Log out a user by removing the related session variables.
	 *
	 * @param   boolean  $destroy     Completely destroy the session
	 * @param   boolean  $logout_all  Logout all browsers
	 * @return  boolean
	 */
	public function logout($destroy = false, $logout_all = false) {

		// Delete the autologin cookie to prevent re-login
		if ($token = Cookie::get($this->_config['cookie_name'])) {
			Cookie::delete($this->_config['cookie_name']);

			$token = new Model_User_Token($token);
			if ($logout_all && $token->loaded()) {
				Model_User_Token::delete_all($token->user_id);
			} else if ($token->loaded()) {
				$token->delete();
			}
		}

		// Logout 3rd party?
		/*
		if (FB::enabled() && Visitor::instance()->get_provider()) {
			$this->session->delete($this->config['session_key'] . '_provider');
			try {
				FB::instance()->expire_session();
			} catch (Exception $e) { }
		}
		*/

		// Destroy the session completely?
		if ($destroy === true) {
			$this->_session->destroy();
		} else {

			// Remove the user from the session
			$this->_session->delete($this->_config['session_key']);

			// Regenerate session_id
			$this->_session->regenerate();

		}

		// Double check
		return !$this->logged_in();
	}

}
