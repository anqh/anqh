<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * View section, container for articles.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2014 Antti Qvickström
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
	 * @var  boolean Supporting content
	 */
	public $aside = false;

	/**
	 * @var  string  Section avatar
	 */
	public $avatar;

	/**
	 * @var  string  ARIA role
	 */
	public $role;

	/**
	 * @var  string  Section subtitle
	 */
	public $subtitle;

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
	 * Get section avatar.
	 *
	 * @return  string
	 */
	public function avatar() {
		return $this->avatar;
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		return implode("\n\n", $this->articles());
	}


	/**
	 * Render <header>.
	 *
	 * @return  string
	 */
	public function header() {
		$title    = $this->title();
		$subtitle = $this->subtitle();
		$tabs     = $this->tabs();
		if ($title || $subtitle || $tabs):
			ob_start();

			$attributes = array(
				'class' => '', //'top attached secondary ui segment'
			);
			if ($this->title_sticky):
				$attributes['class'] .= 'sticky';
			endif;
			if ($avatar = $this->avatar()):
				$attributes['class'] .= ' media';
			endif;

?>

<header<?= HTML::attributes($attributes) ?>>

	<?php if ($title): ?>
	<h4 class="ui <?= ($tabs && $this->tab_style == self::TAB_TAB ? '' : 'dividing') ?> header">

		<?php if ($avatar): ?>
		<?= $avatar ?>
		<?php endif; ?>

		<?= $title ?>

		<?php if ($subtitle): ?>
		<p class="sub header"><?= $subtitle ?></p>
		<?php endif; ?>
	</h4>
	<?php endif; ?>

</header>

<?php if ($tabs): ?>
<div class="ui <?= $this->tab_style == self::TAB_TAB ? 'top attached tabular' : 'compact secondary right aligned' ?> menu">
	<?php foreach ($tabs as $tab): ?>
		<?php if ($tab['url']): ?>
	<?= HTML::anchor($tab['url'], $tab['text'], array('class' => 'item' . ($tab['selected'] ? ' active' : ''))) ?>
		<?php else: ?>
	<?= $tab['tab'] ?>
		<?php endif; ?>
	<?php endforeach; ?>
</div>
<?php endif; ?>

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
		if (Kohana::$profiling === true && class_exists('Profiler', false)):
			$benchmark = Profiler::start('View', __METHOD__ . '(' . get_called_class() . ')');
		endif;

		if ($this->aside):

			$attributes = array(
				'id'    => $this->id,
				'class' => 'ui segment ' . $this->class,
				'role'  => $this->role,
			);

			$render = '<aside' . HTML::attributes($attributes) . '>' . $this->header() . $this->content() . '</aside>';

		else:

			$attributes = array(
				'id'    => $this->id,
				'class' => $this->class,
				'role'  => $this->role,
			);

			$render = '<section' . HTML::attributes($attributes) . '>' . $this->header() . $this->content() . '</section>';

		endif;

		// Stop benchmark
		if (isset($benchmark)):
			Profiler::stop($benchmark);
		endif;

		return $render;
	}


	/**
	 * Get section title.
	 *
	 * @return  string
	 */
	public function subtitle() {
		return $this->subtitle;
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
