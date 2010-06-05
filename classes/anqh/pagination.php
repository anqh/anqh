<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Pagination library
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Pagination extends Kohana_Pagination {

	/**
	 * Go to last page
	 */
	public function last() {
		$this->current_page = $this->total_pages;
		$this->current_first_item = (int)min((($this->current_page - 1) * $this->items_per_page) + 1, $this->total_items);
		$this->current_last_item  = (int)min($this->current_first_item + $this->items_per_page - 1, $this->total_items);
		$this->offset             = (int)($this->current_page - 1) * $this->items_per_page;

		// If there is no first/last/previous/next page, relative to the
		// current page, value is set to FALSE. Valid page number otherwise.
		$this->first_page         = ($this->current_page === 1) ? false : 1;
		$this->last_page          = false;
		$this->previous_page      = ($this->current_page > 1) ? $this->current_page - 1 : false;
		$this->next_page          = false;
	}

}
