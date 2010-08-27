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
	 * Load events between dates
	 * @param   integer  $stamp_begin
	 * @param   integer  $stamp_end
	 * @return  Jelly_Builder
	 */
	public function between($stamp_begin, $stamp_end, $order = 'DESC') {
		$stamp_begin = (int)$stamp_begin;
		$stamp_end   = (int)$stamp_end;

		if (!$stamp_begin || !$stamp_end) {
			throw new Kohana_Exception('Start and end time must be given');
		}

		if ($stamp_begin > $stamp_end) {
			$stamp_temp  = $stamp_begin;
			$stamp_begin = $stamp_end;
			$stamp_end   = $stamp_begin;
		}

		return $this->where('stamp_begin', 'BETWEEN', array($stamp_begin, $stamp_end))->order_by('stamp_begin', $order == 'ASC' ? 'ASC' : 'DESC')->order_by('city_name', 'ASC');
	}


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

			// Sort by city
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
		return $this->where('stamp_begin', '>=', strtotime('today'))->order_by('stamp_begin', 'ASC')->order_by('city_name', 'ASC');
	}

}
