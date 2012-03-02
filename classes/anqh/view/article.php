<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * View article.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2012 Antti Qvickström
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
		if ($meta) {
			ob_start();

?>

<footer class="meta">
	<?php echo $meta ?>
</footer>

<?php

			return ob_get_clean();
		}

		return '';
	}


	/**
	 * Render <header>.
	 *
	 * @return  string
	 */
	public function header() {
		$title   = $this->title();
		$actions = $this->actions();
		if ($title || $actions) {
			ob_start();

?>

<header>

	<?php if ($actions) { ?>
	<div class="btn-group"><?php echo implode(' ', $actions) ?></div>
	<?php } ?>

	<?php if ($title) { ?>
	<h4><?php echo $title ?></h4>
	<?php } ?>

</header>

<?php

			return ob_get_clean();
		}

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
		if (Kohana::$profiling === true and class_exists('Profiler', false)) {
			$benchmark = Profiler::start('View', __METHOD__ . '(' . get_called_class() . ')');
		}

		ob_start();

		// Section attributes
		$attributes = array(
			'id'    => $this->id,
			'class' => $this->class,
		);

?>

<article<?php echo HTML::attributes($attributes) ?>>

	<?php echo $this->header() ?>

	<?php echo $this->content() ?>

	<?php echo $this->footer() ?>

</article>

<?php

		$render = ob_get_clean();

		// Stop benchmark
		if (isset($benchmark)) {
			Profiler::stop($benchmark);
		}

		return $render;
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
