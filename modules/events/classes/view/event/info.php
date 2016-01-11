<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Event side info view.
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Event_Info extends View_Section {

	/**
	 * @var  Model_Event
	 */
	public $event;

	/**
	 * @var  string  Article class
	 */
	public $id = 'event-info';


	/**
	 * Create new article.
	 *
	 * @param  Model_Event  $event
	 */
	public function __construct(Model_Event $event) {
		parent::__construct();

		$this->event = $event;

//		$this->title = HTML::time(Date('l ', $this->event->stamp_begin) . ', ' . Date::format(Date::DMY_LONG, $this->event->stamp_begin), $this->event->stamp_begin, true);
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		/*
		// Stamp
		if ($this->event->stamp_begin != $this->event->stamp_end):
			echo ($this->event->stamp_end ?
				'<i class="icon-time icon-white"></i> ' . __('From :from to :to', array(
					':from' => HTML::time(Date::format('HHMM', $this->event->stamp_begin), $this->event->stamp_begin),
					':to'   => HTML::time(Date::format('HHMM', $this->event->stamp_end), $this->event->stamp_end)
				)) :
				'<i class="icon-time icon-white"></i> ' . __('From :from onwards', array(
					':from' => HTML::time(Date::format('HHMM', $this->event->stamp_begin), $this->event->stamp_begin),
				))) . '<br />';
		endif;
		*/

		/*
		// Price
		if ($this->event->price == 0):
			echo '<i class="icon-shopping-cart icon-white"></i> ' . __('Free entry') . '<br />';
		elseif ($this->event->price > 0):
			echo '<i class="icon-shopping-cart icon-white"></i> ' . __('Tickets :price', array(':price' => '<var>' . Num::currency($this->event->price, $this->event->stamp_begin) . '</var>'));
			echo ($this->event->price2 !== null ? ', ' . __('presale :price', array(':price' => '<var>' . Num::currency($this->event->price2, $this->event->stamp_begin) . '</var>')) : '') . '<br />';
		endif;


		// Age limit
		if ($this->event->age > 0):
			echo '<i class="icon-user icon-white"></i> ' . __('Age limit') . ': ' . __(':years years', array(':years' => '<var>' . $this->event->age . '</var>')) . '<br />';
		endif;
		*/

		/*
		// Homepage
		if (!empty($this->event->url)):
			echo '<i class="icon-home icon-white"></i> ' . HTML::anchor($this->event->url, Text::limit_url($this->event->url, 25)) . '<br />';
		endif;


		// Tags
		if ($tags = $this->event->tags()):
			echo '<i class="icon-music icon-white"></i> ' . implode(', ', $tags) . '<br />';
		elseif (!empty($this->event->music)):
			echo '<i class="icon-music icon-white"></i> ' . $this->event->music . '<br />';
		endif;
		*/


		/*
		// Venue
		if ($_venue = $this->event->venue()):

			// Venue found from db
			$venue   = HTML::anchor(Route::model($_venue), HTML::chars($_venue->name));
			$address = $_venue->address
			 ? HTML::chars($_venue->address) . ', ' . HTML::chars($_venue->city_name)
			 : HTML::chars($_venue->city_name);

			if ($_venue->latitude):
				$map = array(
					'marker'     => HTML::chars($_venue->name),
					'infowindow' => HTML::chars($_venue->address) . '<br />' . HTML::chars($_venue->city_name),
					'lat'        => $_venue->latitude,
					'long'       => $_venue->longitude
				);
				Widget::add('foot', HTML::script_source('
head.ready("anqh", function() {
	$("#event-info a[href=#map]").on("click", function toggleMap(event) {
		$("#map").toggle("fast", function openMap() {
			$("#map").googleMap(' .  json_encode($map) . ');
		});

		return false;
	});
});
'));
			endif;

		elseif ($this->event->venue_name):

			// No venue in db
			$venue   = $this->event->venue_url
				? HTML::anchor($this->event->venue_url, HTML::chars($this->event->venue_name))
				: HTML::chars($this->event->venue_name);
			$address = HTML::chars($this->event->city_name);

		else:

			// Venue not set
			$venue   = $this->event->venue_hidden ? __('Underground') : __('(Unknown)');
			$address = HTML::chars($this->event->city_name);

		endif;
		echo '<address><strong><i class="icon-map-marker icon-white"></i> ', $venue, '</strong>' . ($address ? ', ' . $address : '') . '<br />';
		if (isset($map)):
			echo HTML::anchor('#map', __('Toggle map')) . '<br />';
			echo '<div id="map" style="display: none">', __('Map loading') . '</div>';
		endif;
		echo '</address>';
		*/

		// Meta
		echo '<br /><footer class="meta">';
		echo __('Added') . ' ' . HTML::time(Date::format(Date::DMY_SHORT, $this->event->created), $this->event->created);
		if ($this->event->modified):
			echo ', ' . __('last modified') . ' ' . HTML::time(Date::short_span($this->event->modified, false), $this->event->modified);
		endif;
		echo '</footer>';

		return ob_get_clean();
	}

}
