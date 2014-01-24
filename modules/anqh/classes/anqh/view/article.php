<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * View article.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_View_Article extends View_Base {

	/**
	 * @var  array  Article actions
	 */
	public $actions = array();

	/**
	 * @var  string  Article meta data
	 */
	public $meta;

	/**
	 * @var  string  Article subtitle
	 */
	public $subtitle;

	/**
	 * @var  string  Article title
	 */
	public $title;


	/**
	 * Get article actions.
	 *
	 * @return  array
	 */
	public function actions() {
		return $this->actions;
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		return '';
	}


	/**
	 * Render <footer>.
	 *
	 * @return  string
	 */
	public function footer() {
		$meta = $this->meta();
		if ($meta):
			ob_start();

?>

<footer class="text-muted">
	<?= $meta ?>
</footer>

<?php

			return ob_get_clean();
		endif;

		return '';
	}


	/**
	 * Render <header>.
	 *
	 * @return  string
	 */
	public function header() {
		$title    = $this->title();
		$subtitle = $this->subtitle();
		$actions  = $this->actions();
		if ($title || $actions):
			ob_start();

?>

<header>

	<?php if ($actions): ?>
	<div class="btn-group pull-right"><?= implode(' ', $actions) ?></div>
	<?php endif; ?>

	<?php if ($title): ?>
	<h4><?= $title ?></h4>
	<?php endif; ?>

	<?php if ($subtitle): ?>
	<sup class="text-muted"><?= $subtitle ?></sup>
	<?php endif; ?>

</header>

<?php

			return ob_get_clean();
		endif;

		return '';
	}


	/**
	 * Get article meta data.
	 *
	 * @return  string
	 */
	public function meta() {
		return $this->meta;
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
			'class' => $this->class,
		);

?>

<article<?= HTML::attributes($attributes) ?>>

	<?= $this->header() ?>

	<?= $this->content() ?>

	<?= $this->footer() ?>

</article>

<?php

		$render = ob_get_clean();

		// Stop benchmark
		if (isset($benchmark)):
			Profiler::stop($benchmark);
		endif;

		return $render;
	}


	/**
	 * Get article title.
	 *
	 * @return  string
	 */
	public function subtitle() {
		return $this->subtitle;
	}


	/**
	 * Get article title.
	 *
	 * @return  string
	 */
	public function title() {
		return $this->title;
	}

}
