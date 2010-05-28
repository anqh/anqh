<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Abstract Anqh Template controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Controller_Template extends Kohana_Controller_Template {

	/**
	 * AJAX-like request?
	 *
	 * @var  bool
	 */
	protected $ajax = false;

	/**
	 * Add current page to history
	 *
	 * @var  bool
	 */
	protected $history = true;

	/**
	 * Current page class
	 *
	 * @var  string
	 */
	protected $page_class;

	/**
	 * Current page id, defaults to controller name
	 *
	 * @var  string
	 */
	protected $page_id;

	/**
	 * Page main content position
	 *
	 * @var  string
	 */
	protected $page_main = 'left';

	/**
	 * Current page subtitle
	 *
	 * @var  string
	 */
	protected $page_subtitle = '';

	/**
	 * Current page title
	 *
	 * @var  string
	 */
	protected $page_title = '';

	/**
	 * Page width setting, 'fixed' or 'liquid'
	 *
	 * @var  string
	 */
	protected $page_width = 'fixed';

	/**
	 * Skin for the site
	 *
	 * @var  string
	 */
	protected $skin;

	/**
	 * Skin files imported in skin, check against file modification time for LESS
	 *
	 * @var  array
	 */
	protected $skin_imports;


	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		// Controller name as the default page id if none set
		empty($this->page_id) && $this->page_id = $this->request->controller;

		$this->ajax = (Request::$is_ajax || $this->request !== Request::instance());
	}


	/**
	 * Destroy controller
	 */
	public function after() {
		if ($this->ajax) {

		} else if ($this->auto_render) {

			// Skin
			$skin_path = 'ui/' . Kohana::config('site.skin') . '/';
			$skin = $skin_path . 'skin.less';
			$skin_imports = array(
				'ui/layout.less',
				'ui/widget.less',
				'ui/jquery-ui.css',
				'ui/site.css',
				$skin_path . 'jquery-ui.css',
			);

			// Do some CSS magic to page class
			$page_class = explode(' ',
				$this->language . ' ' .        // Language
				$this->page_width . ' ' .      // Fixed/liquid layout
				$this->page_main . ' ' .       // Left/right aligned layout
				$this->request->action . ' ' . // Controller method
				$this->page_class);
			$page_class = implode(' ', array_unique(array_map('trim', $page_class)));

			// Bind the generic page variables
			$this->template
				->set('skin',          $skin)
				->set('skin_imports',  $skin_imports)
				//->set('stylesheets',   $this->stylesheets)
				->set('language',      $this->language)
				->set('page_id',       $this->page_id)
				->set('page_class',    $page_class)
				->set('page_title',    $this->page_title)
				->set('page_subtitle', $this->page_subtitle);

		}

		parent::after();
	}

}
