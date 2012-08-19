<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Error controller.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Error extends Controller_Page {

	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		$this->history = false;

		// Always render page
		$this->view = new View_ErrorPage(__('Something wonky just happened'));

		// Show always 404 for now
		return $this->request->action(404);

		/*
		if ($this->_request_type !== Controller::REQUEST_AJAX) {

			// External requests show always 404
			return $this->request->action(404);

		} else {

			// Internal requests only
			if ($message = rawurldecode($this->request->param('message'))) {
				$this->view->add(View_Page::COLUMN_TOP, $message);
			}

		}

		$this->response->status((int)$this->request->action());
		*/
	}


	/**
	 * Destroy controller
	 */
	public function after() {
		$this->response->body($this->view);
	}


	/**
	 * Action: 403 Forbidden
	 */
	public function action_403() {
		$this->response->status(403);
		$this->response->body($this->request->param('message'));

		exit;
	}


	/**
	 * Action: 404 Not found
	 */
	public function action_404() {
		$this->response->status(404);

		// Log broken links inside our own site
		if (isset($_SERVER['HTTP_REFERER']) && strstr($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) !== false) {
			Kohana::$log->add(Log::INFO, 'Broken link at ' . $_SERVER['HTTP_REFERER']);
		}

		$this->view->title = __('404 - le fu.');
	}


	/**
	 * Action: 500 Internal server error
	 */
	public function action_500() {
		$this->response->status(200);
		$this->view->title = __('Internal Server Fu.');
	}


	/**
	 * Action: 503 Service unavailable
	 */
	public function action_503() {
		$this->view->title = __('Maintenance Mode');
	}

}
