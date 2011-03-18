<?php defined('SYSPATH') or die('No direct script access.');
/**
 * User Token model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_User_Token extends AutoModeler {

	protected $_table_name = 'user_tokens';

	protected $_data = array(
		'id'         => null,
		'token'      => null,
		'user_id'    => null,
		'user_agent' => null,
		'created'    => null,
		'expires'    => null,
	);


	/**
	 * Load token
	 *
	 * @param  integer|string  $id
	 */
	public function __construct($id = null) {
		parent::__construct();

		if ($id !== null) {
			$this->load(DB::select()->where(is_numeric($id) ? 'id' : 'token', '=', $id));
		}

		// Garbace collection
		if (mt_rand(1, 100) === 1) {
			self::gc();
		}
	}


	/**
	 * Garbage collect
	 *
	 * @static
	 */
	public static function gc() {
		static $collected = false;

		if (!$collected) {
			$collected = true;
			DB::delete('user_tokens')
				->where('expires', '<', time())
				->execute();
		}

	}


	/**
	 * Create new user token
	 *
	 * @return  boolean
	 */
	public function create() {
		$this->created    = time();
		$this->user_agent = sha1(Request::$user_agent);
		$this->token      = $this->create_token();

		return parent::save();
	}


	/**
	 * Find new unique token
	 *
	 * @return  string
	 */
	public function create_token() {
		while (true) {

			// Create random token
			$token = Text::random('alnum', 32);

			// Make sure it's unique
			if (!$this->unique_key_exists($token, 'token')) {
				return $token;
			}

		}
	}


	/**
	 * Delete all user tokens from user
	 *
	 * @static
	 * @param   integer $user_id
	 */
	public static function delete_all($user_id) {
		DB::delete('user_tokens')
			->where('user_id', '=', $user_id)
			->execute();
	}


	/**
	 * Update user token
	 *
	 * @return  boolean
	 */
	public function update() {
		$this->token = $this->create_token();

		return parent::save();
	}
}
