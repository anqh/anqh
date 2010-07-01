<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Comment model builder
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Builder_Comment extends Jelly_Builder {

	/**
	 * Get only comments viewer has access to
	 *
	 * @param   Model_User $user
	 * @return  Jelly_Builder
	 */
	public function viewer(Model_User $user = null) {
		$public = $this->and_where_open()->where('private', '=', '0');
		if ($user) {
			$public = $public->or_where('user_id', '=', $user->id)->or_where('author_id', '=', $user->id);
		}
		$public = $public->and_where_close();

		return $public;
	}

}
