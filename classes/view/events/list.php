<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Events_List view.
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Events_List extends View_Section {

	/**
	 * @var  string  Section class
	 */
	public $class = 'events cut';

	/**
	 * @var  Model_Event[]
	 */
	public $events = null;


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		if (!$this->events) {
			return '';
		}

		ob_start();

?>

<ul class="unstyled">

	<?php foreach ($this->events as $event) { ?>
	<li>
		<time title="<?php echo Date::format(Date::DATETIME, $event->stamp_begin) . ($event->stamp_end ? ' - ' . Date::format(Date::TIME, $event->stamp_end) : '') ?>"><?php echo Date::format(Date::DM_PADDED, $event->stamp_begin) ?></time>
		<?php echo HTML::anchor(Route::model($event), HTML::chars($event->name), array('class' => 'hoverable', 'title' => HTML::chars($event->name))) ?>
	</li>
	<?php } ?>

</ul>

<?php

		return ob_get_clean();
	}

}
