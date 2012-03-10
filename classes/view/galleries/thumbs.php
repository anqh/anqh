<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Galleries_Thumbs
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Galleries_Thumbs extends View_Section {

	/**
	 * @var  boolean  Permission to approve galleries
	 */
	public $can_approve = false;

	/**
	 * @var  Model_Gallery[]
	 */
	public $galleries;

	/**
	 * @var  boolean  List pending galleries
	 */
	public $show_pending = false;


	/**
	 * Create new view.
	 *
	 * @param  Model_Gallery[]  $galleries
	 */
	public function __construct($galleries) {
		parent::__construct();

		$this->galleries = $galleries;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

<ul class="thumbnails">

	<?php foreach ($this->galleries as $gallery): $default_image = $gallery->default_image(); ?>
	<li class="span2">
		<?= HTML::anchor(
			Route::model($gallery, $this->show_pending ? 'pending' : null),
			$default_image ? HTML::image($default_image->get_url('thumbnail', $gallery->dir)) : __('Thumbnail pending'),
			array('class' => 'thumbnail')
		) ?>

		<h4><?= HTML::anchor(Route::model($gallery, $this->show_pending ? 'pending' : null), HTML::chars($gallery->name)) ?></h4>

<?php

			if ($this->show_pending):

				// Approval process
				$copyrights = array();
				$pending_images = $gallery->find_images_pending($this->can_approve ? null : self::$_user);
				foreach ($pending_images as $image) $copyrights[$image->author_id] = $image->author();
				foreach ($copyrights as &$copyright) $copyright = HTML::user($copyright);

				echo '&copy; ', implode(', ', $copyrights), '<br />';
				echo '<i class="icon-camera"></i> ', count($pending_images);

			else:

				// Rating
				if ($gallery->rate_count > 0):
					echo HTML::rating($gallery->rate_total, $gallery->rate_count, false, true, true), '<br />';
				endif;

				// Image count
				echo '<i class="icon-camera"></i> ', $gallery->image_count;

				// Comment count
				if ($gallery->comment_count > 0):
					echo '<i class="icon-comment"></i> ', $gallery->comment_count;
				endif;

				// Copyright
				if ($gallery->copyright):
					$copyrights = explode(',', $gallery->copyright);
					foreach ($copyrights as &$copyright) $copyright = HTML::user(trim($copyright));
					echo '<br />&copy; ', implode(', ', $copyrights);
				endif;

			endif;

?>

	</li>
	<?php endforeach; ?>

</ul>

<?php

		return ob_get_clean();
	}

}
