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

		if ((!count($flyers) || !$flyers->current()->image()) && $this->event->flyer_front_url):

			// Legacy support
			$flyer_url = ($flyer = $flyers->current()) ? $flyer->image_url() : $this->event->flyer_front_url;

			echo HTML::image($flyer_url, [ 'class' => 'img-responsive' ]);

		elseif (count($flyers)):

			// Check for actions
			if (Permission::has($this->event, Model_Event::PERMISSION_UPDATE)):
				$action_uri = Route::model($this->event, 'flyer');
			endif;

			// Check for missing default image
			$active_id = $this->event->flyer_id;
			if (!$active_id):
				$active_id = $flyers->current()->id;
			endif;

			$slide = 0;

?>

<div class="carousel">

	<?php if (count($flyers) > 1): ?>
	<ol class="carousel-indicators">

		<?php foreach ($flyers as $flyer): ?>
		<li data-target="#<?= $this->id ?>" data-slide-to="<?= $slide++ ?>"<?= $flyer->id == $active_id ? ' class="active"' : '' ?>></li>
		<?php endforeach ?>

	</ol>
	<?php endif; ?>

	<div class="carousel-inner">

		<?php foreach ($flyers as $flyer): if ($flyer->image()): ?>

		<div class="item<?= $flyer->id == $active_id ? ' active' : '' ?>">

			<?= HTML::anchor(Route::model($flyer), HTML::image($flyer->image()->get_url(), [ 'class' => 'img-responsive' ])) ?>

			<?php if (isset($action_uri)): ?>

			<div class="actions">
				<?php if ($flyer->id == $this->event->flyer_id):
					echo HTML::anchor('#', '<i class="fa fa-picture-o"></i> ' . __('Set as default'), array('class' => 'btn btn-default btn-xs image-change disabled'));
				else:
					echo HTML::anchor($action_uri . '?token=' . Security::csrf() . '&default=' . $flyer->id, '<i class="fa fa-picture-o"></i> ' . __('Set as default'), array('class' => 'btn btn-default btn-xs image-change'));
				endif; ?>
				<?= HTML::anchor($action_uri . '?token=' . Security::csrf() . '&delete=' . $flyer->id, '<i class="fa fa-trash-o"></i> ' . __('Delete'), array('class' => 'btn btn-default btn-xs image-delete')) ?>
			</div>

			<?php endif; ?>

		</div>

		<?php endif; endforeach; ?>

	</div>

	<?php if (count($flyers) > 1): ?>
	<a class="carousel-control left" href="#<?= $this->id ?>" data-slide="prev"><i class="fa fa-chevron-left icon-prev"></i></a>
	<a class="carousel-control right" href="#<?= $this->id ?>" data-slide="next"><i class="fa fa-chevron-right icon-next"></i></a>
	<?php endif; ?>

</div>

<?php

		elseif (Permission::has($this->event, Model_Event::PERMISSION_UPDATE)):

			// Add new flyer
			echo HTML::anchor(Route::model($this->event, 'flyer'), '<i class="fa fa-picture-o"></i> ' . __('Upload flyer'), array('class' => 'empty ajaxify'));

		endif;

		return ob_get_clean();
	}

}
