<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Events API controller
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Events_API extends Controller_API {

	/**
	 * Action: event
	 */
	public function action_event() {
		$event_id = (int)$_REQUEST['id'];

		// Load event
		$event = Jelly::select('event')->load($event_id);
		if ($event->loaded()) {
			$this->data['events'] = array($this->_prepare_event($event));
		} else {
			$this->data['events'] = array();
		}

	}


	/**
	 * Action: search
	 */
	public function action_search() {
	}


	/**
	 * Prepare event for data array
	 *
	 * @param   Model_Event  $event
	 * @return  array
	 */
	protected function _prepare_event(Model_Event $event) {
		$data = array(
			'id'             => $event->id,
			'name'           => $event->name,
			'homepage'       => $event->homepage,
			'stamp_begin'    => $event->stamp_begin,
			'stamp_end'      => $event->stamp_end,
			'venue'          => $event->venue->id ? $event->venue->name : $event->venue_name,
			'city'           => $event->city->id ? $event->city->name : $event->city_name,
			'country'        => $event->country->id ? $event->country->name : '',
			'performers'     => $event->dj,
			'info'           => $event->info,
			'age'            => $event->age,
			'price'          => $event->price,
			'price2'         => $event->price2,
			'created'        => $event->created,
			'modified'       => $event->modified,
			'flyer_front'    => $event->flyer_front_url,
			'flyer_back'     => $event->flyer_front_url,
			'favorite_count' => $event->favorite_count
		);

		return $data;
	}

}
