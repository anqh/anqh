<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Error controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Error extends Controller_Template {

	/**
	 * Controller default action
	 */
	public function action_index() {

	}

	/**
	 * Action: 404
	 */
	public function action_404() {
		$this->request->status = 404;
		$this->page_title = __('404 - le fu.');
	}

}
