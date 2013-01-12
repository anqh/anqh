<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Events_Days view.
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Events_Days extends View_Section {

	/**
	 * @var  string  Section class
	 */
	public $class = 'events';

	/**
	 * @var  array  Events grouped by date and city
	 */
	public $events = null;


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

		<dl>

			<?php foreach ($this->_events() as $date => $day) { ?>

			<dt<?php if ($date == date('Y-m-d')) echo ' class="today"' ?>>
				<h4>
					<time>
						<span class="weekday"><?php echo $day['date']['weekday_short'] ?></span>
						<span class="day"><?php echo $day['date']['day'] ?></span>
						<span class="month"><?php echo $day['date']['month_short'] ?></span>
					</time>
				</h4>
			</dt>

			<dd>
				<ul>

				<?php foreach ($day['cities'] as $city => $events) { ?>
					<?php foreach ($events as $event) { ?>
					<li class="city-<?php echo URL::title($city) ?>">
						<a href="<?php echo $event['url'] ?>" class="avatar"><?php echo $event['icon'] ?></a>
						<header><span class="city"><?php echo $city ?></span><a href="<?php echo $event['url'] ?>"><?php echo $event['name'] ?></a></header>
						<p class="details"><?php echo $event['price'], ' ', $event['venue'] ?></p>
						<p class="djs"><?php echo $event['dj'] ?></p>
						<footer class="meta"><?php echo $event['music'] ?></footer>
					</li>
					<?php } // events ?>
				<?php } // city ?>

				</ul>
			</dd>
			<?php } // events ?>

		</dl>


<?php

		return ob_get_clean();
	}


	/**
	 * Var method for events.
	 *
	 * @return  array
	 */
	protected function _events() {
		$events = array();
		if ($this->events) {
			foreach ($this->events as $_day => $_cities) {
				$day = array();

				foreach ($_cities as $_city => $_events) {
					$city = array();

					/** @var  Model_Event  $_event */
					foreach ($_events as $_event) {

						// Get venue
						if ($_event->venue_hidden) {
							$venue = __('Underground');
						} else if ($venue  = $_event->venue()) {
							$venue = HTML::anchor(Route::model($venue), $venue->name);
						} else if ($_event->venue_name) {
							$venue = HTML::chars($_event->venue_name);
						} else {
							$venue = '';
						}

						// Get icon
						if ($image = $_event->flyer_front()) {
							$icon = $image->get_url($image::SIZE_ICON);
						} else if ($image = $_event->flyer_back()) {
							$icon = $image->get_url($image::SIZE_ICON);
						} else if (count($flyers = $_event->flyers())) {
							$image = $flyers[0]->image();
							$icon  = $image->get_url($image::SIZE_ICON);
						} else {
							$icon = null;
						}


						$city[] = array(
							'name'  => $_event->name,
							'url'   => Route::model($_event),
							'city'  => $_event->city_name,
							'price' => $_event->price !== null && $_event->price == 0 ?
								__('Free!') :
								($_event->price > 0 ? Num::format($_event->price, 2, true) . '€' :	''),
							'venue' => $venue,
							'icon'  => $icon ? HTML::image($icon, array('alt' => __('Flyer'))) : '',
							'dj'    => $_event->dj,
							'music' => ($tags = $_event->tags()) ? implode(', ', $tags) : $_event->music,
						);
					}

					$day[$_city] = $city;
				}

				$events[$_day] = array(
					'date'   => Date::split($_day),
					'cities' => $day,
				);
			}
		}

		return $events;
	}

}
