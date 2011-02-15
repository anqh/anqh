<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Model_Builder model builder
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Builder extends Jelly_Core_Builder {

	/**
	 * Pagination builder
	 *
	 * @param   Pagination  $pagination
	 * @return  Jelly_Builder
	 */
	public function pagination(Pagination $pagination) {
		return $this->limit($pagination->items_per_page)->offset($pagination->offset);
	}

}
