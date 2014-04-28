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
	 * @var  integer
	 */
	public $rank;

	/**
	 * @var  integer
	 */
	public $rank_last;

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

		$this->id    = 'music-' . $track->id;
		$this->title = HTML::anchor(Route::model($track), HTML::chars($track->name));
		$author = $this->track->author();
		$this->subtitle = HTML::user($author, null, null, Route::url('profile_music', array('username' => urlencode($author['username']))));

		// Meta
		if ($tags = $track->tags()) {
			$this->meta = '<small>' . implode(', ', $tags) . '</small>';
		} else if ($track->music) {
			$this->meta = '<small>' . $track->music . '</small>';
		}
	}


	/**
	 * Get rank change.
	 *
	 * @return  string
	 */
	protected function _change() {
		if ($this->rank_last === false) {

			// No previous rank
			$class  = 'new';
			$change = __('New');

		} else {

			// Rank changed
			$change = $this->rank_last - $this->rank;
			if ($change < 0) {
				$class  = 'text-danger';
				$change = '<i class="fa fa-arrow-down"></i>' . abs($change);
			} else if ($change > 0) {
				$class  = 'text-success';
				$change = '<i class="fa fa-arrow-up"></i>' . $change;
			} else  {
				$class  = 'text-muted';
				$change = '-';
			}
		}

		return '<small class="pull-right rank ' . $class . '">' . $change . '</small>';
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
		$rank = $this->rank ? '<div class="rank">' . $this->rank . '</div>' : '';

		return HTML::anchor(
			Route::model($this->track),
			($icon ? HTML::image($icon, array('alt' => __('Cover')), null, null, true) : '<i class="fa fa-music"></i>') . $rank
		);
	}


	/**
	 * Render article.
	 *
	 * @return  string
	 */
	public function render() {
		if ($this->rank && $this->rank_last !== null) {
			$this->title = $this->_change() . $this->title;
		}

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
