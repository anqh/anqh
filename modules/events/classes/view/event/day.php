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
	public $class = 'event panel media';

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
		$this->title = HTML::anchor(Route::model($event), HTML::chars($event->name));

		// Venue
		if ($this->event->venue_hidden):
			$this->subtitle = __('Underground') . ', ' . HTML::chars($this->event->city_name);
		elseif ($venue  = $this->event->venue()):
			$this->subtitle = HTML::anchor(Route::model($venue), HTML::chars($venue->name)) . ', ' . HTML::chars($venue->city_name);
		else:
			$this->subtitle = HTML::chars($this->event->venue_name . ', ' . $this->event->city_name);
		endif;


		// Meta
		if ($tags = $event->tags()) {
			$this->meta = '<small>' . implode(', ', $tags) . '</small>';
		} else if ($event->music) {
			$this->meta = '<small>' . $event->music . '</small>';
		}

	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {

		// Show limited amount of data
		$info      = Text::limit_chars($this->event->info, 300);
		$show_more = $info != $this->event->info;

		// Max 3 lines
		$lines     = explode("\n", str_replace(array("\r\n", "\r"), "\n", $info), 3);
		$show_more = $show_more || count($lines) == 3;
		if (count($lines) > 2):
			$lines[2] .= '…';
		endif;
		$info = implode("\n", $lines);

		return '<div class="djs">' . BB::factory($info)->render(null, true) . '</div>';
	}


	/**
	 * Get favorites.
	 *
	 * @return  array
	 */
	public function actions() {

		// Clickable favorites
		if (Permission::has($this->event, Model_Event::PERMISSION_FAVORITE, Visitor::$user)):
			if ($this->event->is_favorite(Visitor::$user)):

				// Favorite event, click to unfavorite
				return array(HTML::anchor(
					Route::model($this->event, 'unfavorite') . '?token=' . Security::csrf(),
					$this->event->favorite_count . ' <i class="fa fa-heart"></i>',
					array('title' => __('Remove favorite'), 'class' => 'ajaxify btn btn-xs btn-lovely')
				));

			else:

				// Non-favorite event, click to favorite
				if ($this->event->favorite_count > 1):
					return array(HTML::anchor(
						Route::model($this->event, 'favorite') . '?token=' . Security::csrf(),
						$this->event->favorite_count . ' <i class="fa fa-heart"></i>',
						array('title' => __('Add to favorites'), 'class' => 'ajaxify btn btn-xs btn-default')
					));
				else:
					return array(HTML::anchor(
						Route::model($this->event, 'favorite') . '?token=' . Security::csrf(),
						'<i class="fa fa-heart"></i>',
						array('title' => __('Add to favorites'), 'class' => 'ajaxify btn btn-xs btn-default text-muted')
					));
				endif;

			endif;
		endif;

		return $this->event->favorite_count
			? array('<span class="btn btn-xs btn-default disabled"><i class="fa fa-heart"></i> ' . $this->event->favorite_count . '</span>')
			: null;
	}


	/**
	 * Render flyer.
	 *
	 * @return  string
	 */
	public function flyer() {
		$icon = ($flyer = $this->event->flyer())
			? $flyer->image()->get_url(Model_Image::SIZE_THUMBNAIL)
			: null;

		return HTML::anchor(
			Route::model($this->event),
			$icon ? HTML::image($icon, array('alt' => __('Flyer'))) : '<i class="fa fa-calendar"></i>'
		);
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
	<div class="pull-left flyer"><?= $this->flyer() ?></div>

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
