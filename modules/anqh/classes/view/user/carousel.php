	<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * User image carousel.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_User_Carousel extends View_Section {

	/**
	 * @var  string  View id
	 */
	public $id = 'carousel';

	/**
	 * @var  Model_User
	 */
	public $user;


	/**
	 * Create new view.
	 *
	 * @param  Model_User  $user
	 */
	public function __construct(Model_User $user = null) {
		parent::__construct();

		$this->user = $user;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		// Load images
		$images = $this->user->images();

		// Is Facebook image set
		$facebook = !!strpos($this->user->picture, 'facebook.com');

		// Legacy support
		if ($this->user->picture && !count($images) && !$facebook):
			echo HTML::image($this->user->picture);
		endif;

		if (count($images) || $facebook):
			$count = count($images);
			if ($facebook):
				$count++;
			endif;

			// Check for actions
			if (Permission::has($this->user, Model_User::PERMISSION_UPDATE, Visitor::$user)):
				$action_uri = URL::user($this->user, 'image');
			endif;

			// Check for missing default image
			$active_id = $this->user->default_image_id;
			if (!$active_id && !$facebook):
				$image = $images->current();
				$active_id = $image->id;
			endif;

			$slide = 0;
?>

<div class="carousel">

	<?php if ($count > 1): ?>
	<ol class="carousel-indicators">

			<?php if ($facebook): ?>
		<li data-target="#facebook" data-slide-to="<?= $slide++ ?>" class="active"></li>
			<?php endif; ?>

			<?php foreach ($images as $image): ?>
		<li data-target="#<?= $this->id ?>" data-slide-to="<?= $slide++ ?>"<?= $image->id == $active_id ? ' class="active"' : '' ?>></li>
			<?php endforeach ?>

	</ol>
	<?php endif; ?>

	<div class="carousel-inner">

			<?php if ($facebook): ?>
		<div class="item active">

					<?= HTML::image($this->user->picture) ?>

					<?php if (isset($action_uri)): ?>
			<div class="actions">
				<?= HTML::anchor($action_uri . '?token=' . Security::csrf() . '&delete=facebook', '<i class="fa fa-trash-o"></i> ' . __('Delete'), array('class' => 'btn btn-xs btn-default image-delete')) ?>
			</div>
					<?php endif; ?>

		</div>
			<?php endif; ?>

			<?php foreach ($images as $image): ?>

		<div class="item<?= $image->id == $active_id ? ' active' : '' ?>">

			<?= HTML::image($image->get_url()) ?>

				<?php if (isset($action_uri)): ?>

			<div class="actions">
					<?php if ($image->id == $this->user->default_image_id):
						echo HTML::anchor('#', '<i class="fa fa-home"></i> ' . __('Set as default'), array('class' => 'btn btn-xs btn-default image-change disabled'));
					else:
						echo HTML::anchor($action_uri . '?token=' . Security::csrf() . '&default=' . $image->id, '<i class="fa fa-user"></i> ' . __('Set as default'), array('class' => 'btn btn-xs btn-default image-change'));
					endif; ?>
				<?= HTML::anchor($action_uri . '?token=' . Security::csrf() . '&delete=' . $image->id, '<i class="fa fa-trash-o"></i> ' . __('Delete'), array('class' => 'btn btn-xs btn-default image-delete')) ?>
			</div>

				<?php endif; ?>

		</div>

			<?php endforeach; ?>

	</div>

	<?php if ($count > 1): ?>
	<a class="carousel-control left" href="#<?= $this->id ?>" data-slide="prev"><i class="fa fa-chevron-left icon-prev"></i></a>
	<a class="carousel-control right" href="#<?= $this->id ?>" data-slide="next"><i class="fa fa-chevron-right icon-next"></i></a>
	<?php endif; ?>

</div>

<br>

<?php

		endif;

		// Image search
		echo HTML::anchor(
			Route::url('galleries', array('action' => 'search')) . '?user=' . urlencode($this->user->username),
			'<i class="fa fa-search"></i> ' . __('Search from galleries'),
			array('class' => 'btn btn-default btn-block')
		);

		return ob_get_clean();
	}

}
