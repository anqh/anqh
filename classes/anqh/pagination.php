<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Pagination library
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Pagination extends Kohana_Pagination {

	/**
	 * Go to page with item
	 *
	 * @param   integer  $item
	 * @return  Pagination
	 */
	public function item($item) {
		$this->current_page = null;
		$this->config['current_page']['page'] = ceil((int)$item / $this->items_per_page);

		return $this->setup();
	}


	/**
	 * Go to last page
	 */
	public function last() {
		$this->current_page = null;
		$this->config['current_page']['page'] = $this->total_pages;

		return $this->setup();
	}


	/**
	 * Generates the full URL for a certain page.
	 *
	 * @param   integer  page number
	 * @return  string   page URL
	 */
	public function url($page = 1) {
		if ($this->config['current_page']['source'] == 'query_string' && $url = Arr::get($this->config, 'url')) {

			// Clean the page number
			$page = max(1, (int) $page);

			// No page number in URLs to first page
			if ($page === 1) {
				$page = NULL;
			}

			return URL::site($url) . URL::query(array($this->config['current_page']['key'] => $page));
		}

		return parent::url($page);
	}


	/**
	 * Renders the pagination links.
	 *
	 * @return  string  pagination output (HTML)
	 */
	public function __toString() {
		try {
			return $this->render();
		} catch (Exception $e) {

			// Display the exception message only if not in production
			ob_start();
			Kohana_Exception::handler($e);

			if (Kohana::$environment == Kohana::PRODUCTION) {
				ob_end_clean();
				return __('An error occured and has been logged.');
			} else {
				return ob_get_clean();
			}
		}
	}

}
