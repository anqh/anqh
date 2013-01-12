<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Static controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Static extends Controller {

	/**
	 * Controller default action
	 */
	public function action_404() {
		$this->request->status = 404;
	}

}
