<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Event view class for day list.
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Event_Day extends View_Article {

	/**
	 * @var  string  Article class
	 */
	public $class = 'event';

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

		$this->id        = 'event-' . $event->id;
		$this->title     = HTML::anchor(Route::model($event), $event->name);
		$this->actions[] = HTML::chars($event->city_name);

		// Flyer
		if ($image = $event->flyer_front()) {
			$icon = $image->get_url($image::SIZE_ICON);
		} else if ($image = $event->flyer_back()) {
			$icon = $image->get_url($image::SIZE_ICON);
		} else if (count($flyers = $event->flyers())) {
			$image = $flyers[0]->image();
			$icon  = $image->get_url($image::SIZE_ICON);
		} else {
			$icon = null;
		}
		if ($icon) {
			$this->prefix = HTML::anchor(Route::model($event), HTML::image($icon, array('alt' => __('Flyer'))), array('class' => 'avatar'));
		}


		// Meta
		if ($tags = $event->tags()) {
			$this->meta = implode(', ', $tags);
		} else if ($event->music) {
			$this->meta = $event->music;
		}

	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {

		// Venue
		if ($this->event->venue_hidden) {
			$venue = __('Underground');
		} else if ($venue  = $this->event->venue()) {
			$venue = HTML::anchor(Route::model($venue), $venue->name);
		} else {
			$venue = HTML::chars($this->event->venue_name);
		}

		// Price
		$price = $this->event->price !== null && $this->event->price == 0 ?
			__('Free!') :
			($this->event->price > 0 ? Num::format($this->event->price, 2, true) . '€' : '');

		ob_start();

?>

	<p class="details"><?php echo $price, ' ', $venue ?></p>
	<p class="djs"><?php echo HTML::chars($this->event->dj) ?></p>

<?php

		return ob_get_clean();
	}

}
