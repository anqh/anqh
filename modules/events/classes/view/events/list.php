<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Events List view.
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Events_List extends View_Section {

	/**
	 * @var  Model_Event[]
	 */
	public $events = null;


	/**
	 * Create new view.
	 *
	 * @param  Model_Event[]  $events
	 */
	public function __construct($events = null) {
		parent::__construct();

		$this->events = $events;
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		if (!$this->events):
			return '';
		endif;

		ob_start();

?>

<ul class="list-unstyled">

	<?php foreach ($this->events as $event): ?>
	<li>
		<span title="<?= Date::format(Date::DATETIME, $event->stamp_begin) . ($event->stamp_end ? ' - ' . Date::format(Date::TIME, $event->stamp_end) : '') ?>"><?= Date::format(Date::DM_PADDED, $event->stamp_begin) ?></span>
		<?= HTML::anchor(Route::model($event), HTML::chars($event->name), array('class' => 'hoverable', 'title' => HTML::chars($event->name))) ?>
	</li>
	<?php endforeach; ?>

</ul>

<?php

		return ob_get_clean();
	}

}
