<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Event model builder
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Builder_Event extends Jelly_Builder {

	/**
	 * Return events as array grouped by date and city
	 *
	 * @return  array
	 */
	public function execute_grouped() {
		$events = $this->execute();

		$grouped = array();
		if (count($events)) {

			// Build grouped array
			foreach ($events as $event) {

				// Date
				$date = date('Y-m-d', $event->stamp_begin);
				if (!isset($grouped[$date])) {
					$grouped[$date] = array();
				}

				// City
				$city = UTF8::ucfirst(mb_strtolower($event->city->loaded() ? $event->city->name : $event->city_name));
				if (!isset($grouped[$date][$city])) {
					$grouped[$date][$city] = array();
				}

				$grouped[$date][$city][] = $event;
			}

			// Sort bt city
			$dates = array_keys($grouped);
			foreach ($dates as $date) {
				ksort($grouped[$date]);

				// Drop empty cities to last
				if (isset($grouped[$date][''])) {
					$grouped[$date][__('Elsewhere')] = $grouped[$date][''];
					unset($grouped[$date]['']);
				}

			}

		}

		return $grouped;;
	}


	/**
	 * Upcoming events
	 *
	 * @return  Jelly_Builder
	 */
	public function past() {
		return $this->where('stamp_begin', '<=', time())->order_by('stamp_begin', 'DESC')->order_by('city_name', 'ASC');
	}

	/**
	 * Upcoming events
	 *
	 * @return  Jelly_Builder
	 */
	public function upcoming() {
		return $this->where('stamp_begin', '>=', time())->order_by('stamp_begin', 'ASC')->order_by('city_name', 'ASC');
	}

}
