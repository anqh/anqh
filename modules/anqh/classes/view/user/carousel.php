<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * User_Carousel
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_User_Carousel extends View_Section {

	/**
	 * @var  string  View classes
	 */
	public $class = 'carousel full';

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

		// Legacy support
		if (!count($images) && $this->user->picture):
			echo HTML::image($this->user->picture);
		endif;

		if (count($images)):

			// Check for actions
			if (Permission::has($this->user, Model_User::PERMISSION_UPDATE, self::$_user)):
				$action_uri = URL::user($this->user, 'image');
			endif;

			// Check for missing default image
			$active_id = $this->user->default_image_id;
			if (!$active_id):
				$image = $images->current();
				$active_id = $image->id;
			endif;

?>

	<div class="carousel-inner">

		<?php foreach ($images as $image): ?>

		<div class="item<?= $image->id == $active_id ? ' active' : '' ?>">

			<?= HTML::image($image->get_url()) ?>

			<?php if (isset($action_uri)): ?>

			<div class="btn-group">
				<?php if ($image->id == $this->user->default_image_id):
					echo HTML::anchor('#', '<i class="icon-home"></i> ' . __('Set as default'), array('class' => 'btn btn-mini image-change disabled'));
				else:
					echo HTML::anchor($action_uri . '?token=' . Security::csrf() . '&default=' . $image->id, '<i class="icon-home"></i> ' . __('Set as default'), array('class' => 'btn btn-mini image-change'));
				endif; ?>
				<?= HTML::anchor($action_uri . '?token=' . Security::csrf() . '&delete=' . $image->id, '<i class="icon-trash"></i> ' . __('Delete'), array('class' => 'btn btn-mini image-delete')) ?>
			</div>

			<?php endif; ?>

		</div>

<?php endforeach; ?>

</div>

<a class="carousel-control left" href="#<?= $this->id ?>" data-slide="prev">&lsaquo;</a>
<a class="carousel-control right" href="#<?= $this->id ?>" data-slide="next">&rsaquo;</a>

<?php

		endif;

		return ob_get_clean();
	}

}
