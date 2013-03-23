<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * View section, container for articles.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_View_Section extends View_Base {

	/** Tab/action styles */
	const TAB_PILL = 'pills';
	const TAB_TAB  = 'tabs';

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
	 * @var  string  Section tab style
	 */
	public $tab_style = self::TAB_PILL;

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
		if ($articles = $this->articles()) {
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
		if ($title || $tabs):
			ob_start();

			$attributes = array();
			if ($this->title_sticky):
				$attributes['class'] = 'sticky';
			endif;

?>

<header<?= HTML::attributes($attributes) ?>>

	<?php if ($title): ?>
	<h3><?= $title ?></h3>
	<?php endif; ?>

	<?php if ($tabs): ?>
	<ul class="nav nav-<?= $this->tab_style ?>">
		<?php foreach ($tabs as $tab): ?>
		<li<?= !empty($tab['selected']) ? ' class="active"' : ''?>><?= $tab['tab'] ?></li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>

</header>

<?php

			return ob_get_clean();
		endif;

		return '';
	}


	/**
	 * Render section.
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
			'role'  => $this->role,
		);
?>

<section<?= HTML::attributes($attributes) ?>>

	<?= $this->header() ?>

	<div>
		<?= $this->content() ?>
	</div>

</section>

<?php

		$render = ob_get_clean();

		// Stop benchmark
		if (isset($benchmark)):
			Profiler::stop($benchmark);
		endif;

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
