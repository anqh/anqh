<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Event view class for day list.
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2012 Antti Qvickström
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

		$this->id    = 'event-' . $event->id;
		$this->title = HTML::anchor(Route::model($event), HTML::chars($event->name)) . ' <small>' . HTML::chars($event->city_name) . '</small>';

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
			$venue = HTML::anchor(Route::model($venue), HTML::chars($venue->name));
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


	/**
	 * Render flyer.
	 *
	 * @return  string
	 */
	public function flyer() {
		if ($image = $this->event->flyer_front()) {
			$icon = $image->get_url($image::SIZE_ICON);
		} else if ($image = $this->event->flyer_back()) {
			$icon = $image->get_url($image::SIZE_ICON);
		} else if (count($flyers = $this->event->flyers())) {
			$image = $flyers[0]->image();
			$icon  = $image->get_url($image::SIZE_ICON);
		} else {
			$icon = null;
		}

		return $icon ? HTML::anchor(Route::model($this->event), HTML::image($icon, array('alt' => __('Flyer'))), array('class' => 'avatar')) : '&nbsp;';
	}


	/**
	 * Render article.
	 *
	 * @return  string
	 */
	public function render() {

		// Start benchmark
		if (Kohana::$profiling === true and class_exists('Profiler', false)) {
			$benchmark = Profiler::start('View', __METHOD__ . '(' . get_called_class() . ')');
		}

		ob_start();

		// Section attributes
		$attributes = array(
			'id'    => $this->id,
			'class' => 'row ' . $this->class,
		);

?>

<article<?php echo HTML::attributes($attributes) ?>>
	<div class="span1">

		<?php echo $this->flyer() ?>

	</div>

	<div class="span7">

		<?php echo $this->header() ?>

		<?php echo $this->content() ?>

		<?php echo $this->footer() ?>

	</div>
</article>

<?php

		$render = ob_get_clean();

		// Stop benchmark
		if (isset($benchmark)) {
			Profiler::stop($benchmark);
		}

		return $render;
	}

}
