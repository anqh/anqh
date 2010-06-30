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
	 * Action: 404
	 */
	public function action_404() {
		$this->history = false;
		$this->auto_render = true;
		$this->internal = false;
		$this->request->status = 404;

		if (!$this->ajax) {
			$this->template = View::factory($this->template);
			$this->page_title = __('404 - le fu.');
		} else {
			$this->response = __('404 - le fu.');
		}
	}

}
