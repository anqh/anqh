<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Share view.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Generic_Share extends View_Section {

	/**
	 * @var  string  Section class
	 */
	public $class = 'share';

	/**
	 * @var  string  Google Analytics
	 */
	public $google_analytics;

	/**
	 * @var  string  Shared title
	 */
	public $title;

	/**
	 * @var  string  Shared url
	 */
	public $url;


	/**
	 * Initialize share.
	 */
	public function _initialize() {
		$this->id = Kohana::config('site.share');
		$this->google_analytics = Kohana::config('site.google_analytics');
	}


	/**
	 * Var method for attributes.
	 *
	 * @return  string
	 */
	public function attributes() {
		$attributes = array();

		// Custom URL
		$url = $this->url ? $this->url : Anqh::open_graph('url');
		if ($url) {
			$attributes['addthis:url'] = $url;
		}

		// Custom title
		$title = $this->title ? $this->title : Anqh::open_graph('title');
		if ($title) {
			$attributes['addthis:title'] = $title;
		}

		return HTML::attributes($attributes);
	}


	/**
	 * Var method for script.
	 *
	 * @return  boolean
	 */
	public function script() {
		static $script = false;

		if (!$script) {
			$script = true;
			return false;
		}

		return true;
	}

}
