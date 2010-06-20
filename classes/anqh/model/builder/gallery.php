<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Gallery model builder
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Builder_Gallery extends Jelly_Builder {

	/**
	 * Latest galleries
	 *
	 * @return  Jelly_Builder
	 */
	public function latest() {
		return $this
			->where('image_count', '>', 0)
			->order_by('updated', 'DESC');
	}


	/**
	 * Galleries by year and month
	 *
	 * @param   integer  $year
	 * @param   integer  $month
	 * @return  Jelly_Builder
	 */
	public function year_month($year, $month) {
		$start = mktime(0, 0, 0, $month, 1, $year);
		$end   = strtotime('+1 month', $start);
		return $this
			->where('image_count', '>', 0)
			->where('date', 'BETWEEN', array($start, $end))
			->order_by('date', 'DESC');
	}

}
