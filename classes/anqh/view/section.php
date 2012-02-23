<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * View section, container for articles.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_View_Section extends View_Base {

	/**
	 * @var  array  View articles
	 */
	public $articles = array();

	/**
	 * @var  string  ARIA role
	 */
	public $role;

	/**
	 * @var  array  Section tabs
	 */
	public $tabs;

	/**
	 * @var  string  Section title
	 */
	public $title;

	/**
	 * @var  boolean  Sticky title
	 */
	public $title_sticky = false;


	/**
	 * Get section articles.
	 *
	 * @return  array
	 */
	public function articles() {
		return $this->articles;
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		if ($articles = &$this->articles()) {
			return implode("\n\n", $articles);
		}

		return '';
	}


	/**
	 * Render <header>.
	 *
	 * @return  string
	 */
	public function header() {
		$title = $this->title();
		$tabs  = $this->tabs();
		if ($title || $tabs) {
			ob_start();

			$attributes = array();
			if ($this->title_sticky) {
				$attributes['class'] = 'sticky';
			}
?>

<header<?php echo HTML::attributes($attributes) ?>>

	<?php if ($title) { ?>
	<h3><?php echo HTML::chars($title) ?></h3>
	<?php } ?>

	<?php if ($tabs) { ?>
	<ul class="nav nav-pills">
		<?php foreach ($tabs as $tab) { ?>
		<li<?php echo !empty($tab['selected']) ? ' class="active"' : ''?>><?php echo $tab['tab'] ?></li>
		<?php } ?>
	</ul>
	<?php } ?>

</header>

<?php

			return ob_get_clean();
		}

		return '';
	}


	/**
	 * Render section.
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
			'role'  => $this->role,
		);
?>

<section<?php echo HTML::attributes($attributes) ?>>

	<?php echo $this->header() ?>

	<div>
		<?php echo $this->content() ?>
	</div>

</section>

<?php

		$render = ob_get_clean();

		// Stop benchmark
		if (isset($benchmark)) {
			Profiler::stop($benchmark);
		}

		return $render;
	}


	/**
	 * Get section tabs.
	 *
	 * @return  array
	 */
	public function tabs() {
		return $this->tabs;
	}


	/**
	 * Get section title.
	 *
	 * @return  string
	 */
	public function title() {
		return $this->title;
	}

}
