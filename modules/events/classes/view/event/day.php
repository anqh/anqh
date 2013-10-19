<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Event view class for day list.
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2013 Antti Qvickström
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
		if ($this->event->venue_hidden):
			$venue = __('Underground');
		elseif ($venue  = $this->event->venue()):
			$venue = HTML::anchor(Route::model($venue), HTML::chars($venue->name));
		else:
			$venue = HTML::chars($this->event->venue_name);
		endif;

		ob_start();

		// Show limited amount of data
		$info      = Text::limit_chars($this->event->info, 500);
		$show_more = $info != $this->event->info;

		// Max 5 lines
		$lines     = explode("\n", str_replace(array("\r\n", "\r"), "\n", $info), 6);
		$show_more = $show_more || count($lines) == 6;
		if (count($lines) > 5) {
			$lines[5] = '…';
		}
		$info = implode("\n", $lines);

		if ($show_more) {
			$info .= ' [url=' . Route::model($this->event) . ']' . __('See more') . '[/url]';
		}

?>

	<span class="details"><?= $this->event->price() . ($venue ? ' @ ' : '') . $venue ?></span><br />
	<span class="djs"><?= BB::factory($info)->render(null, true) ?></span>

<?php

		return ob_get_clean();
	}


	/**
	 * Render favorites.
	 *
	 * @return  string
	 */
	public function favorites() {

		// Clickable favorites
		if (Permission::has($this->event, Model_Event::PERMISSION_FAVORITE, self::$_user)):
			if ($this->event->is_favorite(self::$_user)):

				// Favorite event, click to unfavorite
				return HTML::anchor(
					Route::model($this->event, 'unfavorite') . '?token=' . Security::csrf(),
					'<i class="icon-heart"></i> ' . $this->event->favorite_count,
					array('title' => __('Remove favorite'), 'class' => 'ajaxify btn btn-small btn-lovely active')
				);

			else:

				// Non-favorite event, click to favorite
				if ($this->event->favorite_count):
					return HTML::anchor(
						Route::model($this->event, 'favorite') . '?token=' . Security::csrf(),
						'<i class="icon-heart"></i> ' . $this->event->favorite_count,
						array('title' => __('Add to favorites'), 'class' => 'ajaxify btn btn-small btn-inverse active')
					);
				else:
					return HTML::anchor(
						Route::model($this->event, 'favorite') . '?token=' . Security::csrf(),
						'<i class="muted icon-heart"></i>',
						array('title' => __('Add to favorites'), 'class' => 'ajaxify btn btn-small btn-inverse active')
					);
				endif;

			endif;
		endif;

		return $this->event->favorite_count
			? '<span class="btn btn-small btn-inverse disabled"><i class="icon-heart icon-white"></i> ' . $this->event->favorite_count . '</a>'
			: '';
	}


	/**
	 * Render flyer.
	 *
	 * @return  string
	 */
	public function flyer() {
		if ($image = $this->event->flyer_front()):
			$icon = $image->get_url($image::SIZE_ICON);
		elseif (count($flyers = $this->event->flyers())):
			$image = $flyers[0]->image();
			$icon  = $image->get_url($image::SIZE_ICON);
		else:
			$icon = null;
		endif;

		return $icon
			? HTML::anchor(Route::model($this->event), HTML::image($icon, array('alt' => __('Flyer'))), array('class' => 'avatar'))
			: '<div class="avatar empty"><i class="icon-picture"></i></div>';
	}


	/**
	 * Render article.
	 *
	 * @return  string
	 */
	public function render() {

		// Start benchmark
		if (Kohana::$profiling === true and class_exists('Profiler', false)):
			$benchmark = Profiler::start('View', __METHOD__ . '(' . get_called_class() . ')');
		endif;

		ob_start();

		// Section attributes
		$attributes = array(
			'id'    => $this->id,
			'class' => 'media ' . $this->class,
		);

?>

<article<?= HTML::attributes($attributes) ?>>
	<div class="pull-left">

		<?= $this->flyer() ?>
		<br>
		<?= $this->favorites() ?>

	</div>

	<div class="media-body">

		<?= $this->header() ?>

		<?= $this->content() ?>

		<?= $this->footer() ?>

	</div>
</article>

<?php

		$render = ob_get_clean();

		// Stop benchmark
		if (isset($benchmark)):
			Profiler::stop($benchmark);
		endif;

		return $render;
	}

}
