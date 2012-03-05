<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Event HoverCard
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Event_HoverCard extends View_Article {

	/**
	 * @var  Model_Event
	 */
	public $event;


	/**
	 * Create new view.
	 *
	 * @param  Model_Event  $event
	 */
	public function __construct(Model_Event $event) {
		parent::__construct();

		$this->event = $event;
		$this->title = HTML::chars($this->event->name);
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		// Stamp
		echo HTML::time(Date('l ', $this->event->stamp_begin) . Date::format('DDMMYYYY', $this->event->stamp_begin), $this->event->stamp_begin, true);

		// Location
		if ($this->event->venue):
			echo ' @ ', HTML::anchor(Route::model($this->event->venue), HTML::chars($this->event->venue->name)), ', ', HTML::chars($this->event->venue->city_name);
		elseif ($this->event->venue_name):
			echo ' @ ', ($this->event->venue_url ? HTML::anchor($this->event->venue_url, $this->event->venue_name) : HTML::chars($this->event->venue_name)),
				($this->event->city_name ? ', ' . HTML::chars($this->event->city_name) : '');
		elseif ($this->event->city_name):
			echo ' @ ', HTML::chars($this->event->city_name);
		endif;

		// Flyer
		if ($this->event->flyer_front):
			echo '<figure>', HTML::image($this->event->flyer_front->get_url('thumbnail')), '</figure>';
		elseif ($this->event->flyer_back):
			echo '<figure>', HTML::image($this->event->flyer_back->get_url('thumbnail')), '</figure>';
		elseif (Valid::url($this->event->flyer_front_url)):
			echo '<br /><figure>', HTML::image($this->event->flyer_front_url, array('width' => 160)), '</figure>';
		endif;

		// Favorites
		if ($this->event->favorite_count):
			echo '<span class="favorites"><i class="icon-heart"></i> ' . $this->event->favorite_count . '</span>';
		endif;

		return ob_get_clean();
	}

}
