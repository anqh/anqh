<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Page controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Controller_Page extends Controller {

	/**
	 * Construct controller.
	 */
	public function before() {
		parent::before();

		$this->auto_render = ($this->_request_type !== Controller::REQUEST_INTERNAL);
		$this->breadcrumb  = Session::instance()->get('breadcrumb', array());
		$this->history     = $this->history && !$this->ajax;

		// Load the template
		if ($this->auto_render === true) {
			$this->view = View_Page::factory();
		}

	}


	/**
	 * Destroy controller.
	 */
	public function after() {
		if ($this->_request_type !== Controller::REQUEST_INITIAL) {

			// AJAX and HMVC requests
			$this->response->body((string)$this->response->body());

		} else if ($this->auto_render) {

			// Normal requests

			// Stylesheets
			$styles = array(
				'ui/jquery-ui.css', // Deprecated
				'http://fonts.googleapis.com/css?family=Terminal+Dosis'
			);


			// Skins
			$selected_skin = Session::instance()->get('skin', 'blue');

			// Less files needed to build a skin
			$less_imports = array(
				//'ui/blue.less',
				'ui/mixin.less',
				'ui/anqh.less',
			);

			$skins = array();
			foreach (array('blue') as $skin) {
				$skins[] = Less::style(
					'ui/' . $skin . '.less',
					array(
						'title' => $skin,
						'rel'   => $skin == $selected_skin ? 'stylesheet' : 'alternate stylesheet'
					),
					false,
					$less_imports
				);
			}


			// Do some CSS magic to page class
			$page_class = explode(' ',
				$this->language . ' ' .          // Language
				$this->request->action() . ' ' . // Controller method
				$this->page_class);              // Controller set class
			$page_class = implode(' ', array_unique(array_map('trim', $page_class)));


			// Set the generic page variables
			$this->view->styles   = $styles;
			$this->view->skins    = $skins;
			$this->view->language = $this->language;
			$this->view->id       = $this->page_id;
			$this->view->class    = $page_class;

		}

		parent::after();
	}

}
