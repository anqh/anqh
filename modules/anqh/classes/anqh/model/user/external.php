<?php defined('SYSPATH') or die('No direct script access.');
/**
 * 3rd party user model.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_User_External extends AutoModeler_ORM {
	protected $_table_name = 'user_externals';

	protected $_data = array(
		'id'               => null,
		'token'            => null,
		'user_id'          => null,
		'created'          => null,
		'modified'         => null,
		'expires'          => null,
		'provider'         => null,
		'settings'         => null,
		'external_user_id' => null,
	);


	/**
	 * Load token
	 *
	 * @param  integer|string  $id
	 */
	public function __construct($id = null) {
		parent::__construct();

		if ($id !== null) {
			$this->load(DB::select_array($this->fields())->where(is_numeric($id) ? 'id' : 'token', '=', $id));

			// Expired token?
			if ($this->loaded() && $this->expires < time()) {
				$this->delete();
			}

		}

		// Garbace collection
		/*
		if (mt_rand(1, 100) === 1) {
			self::gc();
		}
		*/
	}


	/**
	 * Get access token array.
	 *
	 * @return  array
	 */
	public function access_token() {
		return array(
			'access_token' => $this->token,
			'expires'      => $this->expires - $this->modified ? $this->modified : $this->created
		);
	}


	/**
	 * Find by external user.
	 *
	 * @param   integer  $user_id
	 * @param   string   $provider
	 * @return  Model_User_External
	 */
	public function find_by_external_user_id($user_id, $provider) {
		return $this->load(DB::select_array($this->fields())
			->where('external_user_id', '=', (int)$user_id)
			->and_where('provider', '=', $provider)
		);
	}


	/**
	 * Find by user.
	 *
	 * @param   integer  $user_id
	 * @param   string   $provider  If given, loads only 1, otherwise all
	 * @return  Model_User_External
	 */
	public function find_by_user_id($user_id, $provider = null) {
		$query = DB::select_array($this->fields())->where('user_id', '=', (int)$user_id);

		return $provider
			? $this->load($query->and_where('provider', '=', $provider))
			: $this->load($query->order_by('expires', 'ASC'), null);
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
			DB::delete('user_externals')
				->where('expires', '<', time())
				->execute();
		}
	}

}
