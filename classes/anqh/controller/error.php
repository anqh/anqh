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
	 * @var  string  Error page template
	 */
	public $template = 'generic/error';


	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		// Always render template
		$this->template = View::factory($this->template);

		// Internal requests only
		if (!$this->request->is_initial()) {
			if ($message = rawurldecode($this->request->param('message'))) {
				$this->template->message = $message;
			}
		} else {

			// External requests show always 404
			$this->request->action(404);

		}

		$this->response->status((int)$this->request->action());
	}


	/**
	 * Destroy controller
	 */
	public function after() {
		$this->response->body($this->template);
	}


	/**
	 * Action: 404
	 */
	public function action_404() {
		$this->response->status(404);

		// Log broken links inside our own site
		if (isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) !== false) {
			Kohana::$log->add(Log::INFO, 'Broken link at ' . $_SERVER['HTTP_REFERER']);
		}

		$this->template->title = __('404 - le fu.');
	}


	/**
	 * Action: 500
	 */
	public function action_500() {
		$this->response->status(200);
		$this->template->title = __('Internal Server Fu.');
	}


	/**
	 * Action: 500
	 */
	public function action_503() {
		$this->template->title = __('Maintenance Mode');
	}

}
