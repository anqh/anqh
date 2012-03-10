<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Gallery_Event
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Gallery_Event extends View_Section {

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
		$this->title = HTML::chars($this->event->name)
			. ' '
			. HTML::time(Date('l ', $this->event->stamp_begin) . Date::format('DDMMYYYY', $this->event->stamp_begin), $this->event->stamp_begin, true);
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		// Line-up
		if ($this->event->dj):

?>

<p class="dj">
		<h3><?= __('Line-up') ?></h3>
		<?= Text::auto_p(HTML::chars($this->event->dj)) ?>
</p>

<?php

		endif;

		echo HTML::anchor(Route::url('galleries', array('action' => 'upload')) . '?from=' . $this->event->id, __('Continue'), array('class' => 'action'));

		return ob_get_clean();
	}

}
