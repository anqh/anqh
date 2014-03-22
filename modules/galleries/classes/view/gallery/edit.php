<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Gallery_Edit
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Gallery_Edit extends View_Section {

	/**
	 * @var  array  Form errors
	 */
	public $errors;

	/**
	 * @var  Model_Event  Bound event
	 */
	public $event;

	/**
	 * @var  Model_Event  Suggested events
	 */
	public $events;


	/**
	 * Create new view.
	 *
	 * @param  Model_Event  $event  Bound event, if any
	 */
	public function __construct(Model_Event $event = null) {
		parent::__construct();

		$this->event = $event;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		// Event form
		echo Form::open(null, array('onsubmit' => 'return false'));

		echo Form::control_group(
			Form::input('name', $this->event->name, array('id' => 'field-name', 'class'=> 'input-xxlarge', 'placeholder' => __('Event name'))),
			array('field-name' => __('Select event for the gallery')),
			Arr::get($this->errors, 'name'),
			__('Enter at least 3 characters')
		);

		echo Form::close();


		// Suggestions
		if ($this->events):

?>

<p>

	<?= __('.. or select one of your recent favorites:') ?>

	<ul class="unstyled">

		<?php foreach ($this->events as $event): ?>
		<li>
			<time title="<?php echo Date::format(Date::DATETIME, $event->stamp_begin) . ($event->stamp_end ? ' - ' . Date::format(Date::TIME, $event->stamp_end) : '') ?>"><?php echo Date::format(Date::DM_PADDED, $event->stamp_begin) ?></time>
			<?php echo HTML::anchor(Route::url('galleries', array('action' => 'upload')) . '?from=' . $event->id, HTML::chars($event->name)) ?>
		</li>
		<?php endforeach; ?>

	</ul>
</p>

<?php endif; ?>

<script>

// Name autocomplete
head.ready('anqh', function() {
	$('#field-name').autocompleteEvent({
		action: function(event, selection) {
			window.location = '<?= URL::site(Route::url('galleries', array('action' => 'upload'))) ?>?from=' + selection.id;
		}
	});
});
</script>

<?php

		return ob_get_clean();
	}

}
