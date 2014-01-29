<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Flyer carousel.
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Event_Carousel extends View_Section {

	/**
	 * @var  Model_Event
	 */
	public $event;

	/**
	 * @var  string  View id
	 */
	public $id = 'carousel';


	/**
	 * Create new view.
	 *
	 * @param  Model_Event  $event
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

		// Load images
		$flyers = $this->event->flyers();

		if (!count($flyers) && $this->event->flyer_front_url):

			// Legacy support
			echo HTML::image($this->event->flyer_front_url);

		elseif (count($flyers)):

			// Check for actions
			if (Permission::has($this->event, Model_Event::PERMISSION_UPDATE, self::$_user)):
				$action_uri = Route::model($this->event, 'image');
			endif;

			// Check for missing default image
			$active_id = $this->event->flyer_front_image_id;
			if (!$active_id):
				$active_id = $flyers->current()->image_id;
			endif;

?>

<div class="carousel">

	<div class="carousel-inner">

		<?php foreach ($flyers as $flyer): ?>

		<div class="item<?= $flyer->image_id == $active_id ? ' active' : '' ?>">

			<?= HTML::anchor(Route::model($flyer), HTML::image($flyer->image()->get_url())) ?>

			<?php if (isset($action_uri)): ?>

			<div class="btn-group">
				<?php if ($flyer->image_id == $this->event->flyer_front_image_id):
					echo HTML::anchor('#', '<i class="fa fa-home"></i> ' . __('Set as default'), array('class' => 'btn btn-default btn-xs image-change disabled'));
				else:
					echo HTML::anchor($action_uri . '?token=' . Security::csrf() . '&default=' . $flyer->image_id, '<i class="fa fa-home"></i> ' . __('Set as default'), array('class' => 'btn btn-default btn-xs image-change'));
				endif; ?>
				<?= HTML::anchor($action_uri . '?token=' . Security::csrf() . '&delete=' . $flyer->image_id, '<i class="fa fa-trash-o"></i> ' . __('Delete'), array('class' => 'btn btn-default btn-xs image-delete')) ?>
			</div>

			<?php endif; ?>

		</div>

		<?php endforeach; ?>

	</div>

	<?php if (count($flyers) > 1): ?>
	<a class="carousel-control left" href="#<?= $this->id ?>" data-slide="prev"><i class="fa fa-chevron-left icon-prev"></i></a>
	<a class="carousel-control right" href="#<?= $this->id ?>" data-slide="next"><i class="fa fa-chevron-right icon-next"></i></a>
	<?php endif; ?>

</div>

<?php

		elseif (Permission::has($this->event, Model_Event::PERMISSION_UPDATE, self::$_user)):

			// Add new flyer
			echo HTML::anchor(Route::model($this->event, 'image'), '<i class="icon-picture icon-white"></i> ' . __('Add flyer'), array('class' => 'empty ajaxify'));

		endif;

		return ob_get_clean();
	}

}
