<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Music Track view.
 *
 * @package    Music
 * @author     Antti Qvickström
 * @copyright  (c) 2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Music_Track extends View_Article {

	/**
	 * @var  string
	 */
	public $class = 'track panel media';

	/**
	 * @var  Model_Music_Track
	 */
	public $track;


	/**
	 * Create new view.
	 *
	 * @param  Model_Music_Track  $track
	 */
	public function __construct($track = null) {
		parent::__construct();

		$this->track = $track;

		$this->id       = 'music-' . $track->id;
		$this->title    = HTML::anchor(Route::model($track), HTML::chars($track->name));
		$this->subtitle = HTML::user($track->author_id);

		// Meta
		if ($tags = $track->tags()) {
			$this->meta = '<small>' . implode(', ', $tags) . '</small>';
		}
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

	<?php if ($this->track->size_time): ?>
	<i class="fa fa-fw fa-clock-o"></i> <?= $this->track->size_time ?><br>
	<?php endif ?>

	<i class="fa fa-fw fa-play"></i> <?= $this->track->listen_count == 1
		? __(':count play', array(':count' => $this->track->listen_count))
		: __(':count plays', array(':count' => $this->track->listen_count)) ?><br>

	<i class="fa fa-fw fa-calendar"></i> <?= __('Added :date', array(':date' => HTML::time(Date::format(Date::DMY_SHORT, $this->track->created), $this->track->created))) ?>
<?php

		return ob_get_clean();
	}


	/**
	 * Render cover.
	 *
	 * @return  string
	 */
	public function cover() {
		$icon = $this->track->cover();

		return HTML::anchor(
			Route::model($this->track),
			$icon ? HTML::image($icon, array('alt' => __('Cover'))) : '<i class="fa fa-music"></i>'
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
	<div class="pull-left cover"><?= $this->cover() ?></div>

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
