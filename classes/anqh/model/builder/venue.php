<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Venue model builder
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Builder_Venue extends Jelly_Builder {

	/**
	 * Get event hosts
	 *
	 * @param   boolean  $host
	 * @return  Jelly_Builder
	 */
	public function event_hosts($host = true) {
		return $this->where('event_host', '=', (bool)$host);
	}

}
