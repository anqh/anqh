<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Shouts view.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Shouts_Shouts extends View_Section {

	/**
	 * @var  integer  Visible shouts
	 */
	public $limit = 10;

	/**
	 * Initialize shouts.
	 */
	public function _initialize() {
		$this->id    = 'shouts';
		$this->title = __('Shouts');
		$this->_routes['shout'] = Route::get('shouts')->uri(array('action' => 'shout'));
	}


	/**
	 * Var method for can_shout.
	 *
	 * @return  string
	 */
	public function can_shout() {
		return Permission::has(new Model_Shout, Model_Shout::PERMISSION_CREATE, self::$user);
	}


	/**
	 * Var method for shouts.
	 *
	 * @return  array
	 */
	public function shouts() {
		$shouts = array();
		foreach (Model_Shout::find_latest($this->limit) as $shout) {
			$shouts[] = array(
				'stamp' => HTML::time(Date::format('HHMM', $shout->created), $shout->created),
			  'user'  => HTML::user($shout->author_id),
			  'text'  => Text::smileys(HTML::chars($shout->shout))
			);
		}

		return array_reverse($shouts);
	}

}
