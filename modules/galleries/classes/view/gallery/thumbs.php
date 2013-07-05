<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Gallery_Thumbs
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Gallery_Thumbs extends View_Section {

	/**
	 * @var  boolean  Permission to approve images
	 */
	public $can_approve = false;

	/**
	 * @var  Model_Gallery
	 */
	public $gallery;

	/**
	 * @var  boolean  Viewing pending images
	 */
	public $show_pending = false;


	/**
	 * Create new view.
	 *
	 * @param  Model_Gallery  $gallery
	 */
	public function __construct(Model_Gallery $gallery) {
		parent::__construct();

		$this->gallery = $gallery;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		// Get shown images
		if ($this->show_pending):

			// Show pending images
			$images = $this->gallery->find_images_pending($this->can_approve ? null : self::$_user);

			$radios = array();
			if ($this->can_approve):
				$radios['approve'] = __('Approve');
			endif;
			$radios['deny'] = $this->can_approve ? __('Deny') : __('Delete');
			$radios['wait'] = __('Wait');

?>

	<header class="well sticky">

	<?php if ($this->can_approve): ?>
		<?= __('Approve') ?>: <var class="approve">0</var>,
		<?= __('Deny') ?>: <var class="deny">0</var>
	<?php else: ?>
		<?= __('Delete') ?>': <var class="deny">0</var>,
	<?php endif; ?>
	<?= __('Wait') ?>: <var class="wait"><?= count($images) ?></var><br />

	</header>

<?php

		else:

			// Show approved images
			$images = $this->gallery->images();

		endif;


		// Add pending images form?
		if ($this->show_pending):
			echo Form::open(null, array('id' => 'form-image-approval', 'class' => 'form-horizontal'));
		endif;

		$copyright = $multiple = null;

		foreach ($images as $image):

			// Add copyright
			if ($image->author_id != $copyright):
				$copyright = $image->author_id;
				if ($multiple):

					// Not first copyright

?>

	</ul>

<?php

				else:

					// First copyright
					$multiple = true;

				endif;

?>

	<header><h3>&copy; <?= HTML::user($copyright) ?></h3></header>
	<ul class="thumbnails">

<?php endif; // Copyright ?>

		<li>

			<a class="thumbnail" href="<?= Route::url('gallery_image', array('gallery_id' => Route::model_id($this->gallery), 'id' => $image->id, 'action' => $this->show_pending ? 'approve' : '')) ?>">
				<?= HTML::image($image->get_url('thumbnail', $this->gallery->dir)) ?>

				<?php if (!$this->show_pending): ?>

					<?php if ($image->description): ?>
				<p class="description"><?= HTML::chars($image->description) ?></p>
					<?php endif; ?>

				<span class="stats">
					<?php if ($image->comment_count): ?>
					<i class="icon-comment icon-white"></i> <?= (int)$image->comment_count ?>
					<?php endif; ?>
					<i class="icon-eye-open icon-white"></i> <?= (int)$image->view_count ?>
				</span>
			</a>

			<?php else: ?>

			</a>

			<?= Form::radios_wrap('image_id[' . $image->id . ']', $radios, 'wait') ?>

			<?php endif; ?>

		</li>

<?php	endforeach; ?>

	</ul>

<?php

		// Form controls
		if ($this->show_pending):

?>

	<fieldset class="form-actions">

<?php

		echo Form::radios_wrap('all', $radios, null, null, __('For all images'), null, null, 'inline');

		echo Form::csrf();

		echo Form::button('approve', __('Save'), array('type' => 'submit', 'class' => 'btn btn-success btn-large')), ' ';
		echo HTML::anchor(Route::url('galleries', array('action' => 'approval')), __('Cancel'), array('class' => 'cancel'));

?>

	</fieldset>

<?php

			echo Form::close();

?>

<script>
head.ready('jquery', function() {

	// Calculate totals
	function totals() {
		$.each([ 'approve', 'deny', 'wait' ], function totals() {
			$('var.' + this).text($('input[name!=all][value=' + this + ']:checked').length);
		});
	}

	// Actions for all images
	$('form input[name=all]').change(function onChangeAll() {
		$('form input[value=' + $(this).val() + ']').attr('checked', 'checked');

		totals();
	});

	// Single image actions
	$('form input[name^=image_id]').change(function onChangeOne() {
		$('input[name=all]').removeAttr('checked');

		totals();
	});

});
</script>


<?php

		endif;

		return ob_get_clean();
	}

}
