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
	 * @var  string  Article prefix
	 */
	public $prefix;

	/**
	 * @var  boolean  Prefix content with empty if $prefix not set
	 */
	public $prefixed = false;

	/**
	 * @var  integer  Article grid width
	 */
	public $span = 8;

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
	 * Render prefix.
	 *
	 * @return  string
	 */
	public function prefix() {
		if ($this->prefix) {
			ob_start();

?>

<div class="span1 prefix">

	<?php echo $this->prefix ?>

</div>

<?php

			return ob_get_clean();
		}

		return '';
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

		// Get prefix
		$prefix = $this->prefix();

		// Grid elements
		$content_grid = 'span' . ($prefix || $this->prefixed ? $this->span - 1 : $this->span);

		// Offset content if prefix wanted but not given
		if ($this->prefixed && !$prefix) {
			$content_grid .= ' offset1';
		}

?>

<article<?php echo HTML::attributes($attributes) ?>>

	<?php echo $prefix ?>

	<div class="<?php echo $content_grid ?> content">
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


	/**
	 * Get article title.
	 *
	 * @return  string
	 */
	public function title() {
		return $this->title;
	}

}
