<?php defined('SYSPATH') or die('No direct script access.');
/**
 * User_Token model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_User_Token extends Jelly_Model {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id' => new Field_Primary,
			'token' => new Field_String(array(
				'unique' => true,
				'rules'  => array(
					'max_length' => array(32)
				)
			)),
			'user' => new Field_BelongsTo,
			'user_agent' => new Field_String,
			'created' => new Field_Timestamp(array(
				'auto_now_create' => true,
			)),
			'expires' => new Field_Timestamp,
		));

		// Garbace collection
		if (mt_rand(1, 100) === 1) {
			Jelly::delete('user_token')->where('expires', '<', time())->execute();
		}
	}


	/**
	 * Create new user token
	 *
	 * @return  boolean
	 */
	public function create() {
		$this->user_agent = sha1(Request::$user_agent);
		$this->token = $this->create_token();

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
			if (!Jelly::select('user_token')->where('token', '=', $token)->count()) {
				return $token;
			}

		}
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
