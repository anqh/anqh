<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Gallery_Links
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2013.2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Gallery_Links extends View_Section {

	/**
	 * @var  array
	 */
	public $errors;

	/**
	 * @var  Model_Gallery
	 */
	public $gallery;


	/**
	 * Create new view.
	 *
	 * @param  Model_Gallery  $gallery
	 */
	public function __construct($gallery = null) {
		parent::__construct();

		$this->title   = __('Other galleries');
		$this->gallery = $gallery;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		if ($this->gallery->links):
			$links = explode("\n", $this->gallery->links);
			$count = 0;

?>

<ul class="list-unstyled">

	<?php	foreach ($links as $link): list($user_id, $url) = explode(',', $link, 2); ?>
	<li>
		<?= HTML::anchor($url, Text::limit_url($url, 75)) ?> &copy; <?= HTML::user($user_id) ?>
		<?php if ($user_id == self::$_user_id || Permission::has($this->gallery, Model_Gallery::PERMISSION_UPDATE, self::$_user)): ?>
		<?= HTML::anchor(Route::model($this->gallery) . '?delete_link=' . $count . '&' . Security::csrf_query(), __('Remove'), array('class' => 'btn btn-danger btn-sm link-delete')) ?>
		<?php endif; ?>
	</li>
	<?php $count++; endforeach ?>

</ul>

<?php

		endif;

		// Add new link
		if (Permission::has($this->gallery, Model_Gallery::PERMISSION_CREATE, self::$_user)):
			echo HTML::anchor('#add-link', '<i class="fa fa-plus-circle"></i> ' . __('Add link'), array('data-toggle' => 'collapse', 'data-target' => '#form-link'));
			echo $this->form();
		endif;

		return ob_get_clean();
	}


	/**
	 * Add link form.
	 *
	 * @return  string
	 */
	public function form() {
		ob_start();

		echo Form::open(null, array('id' => 'form-link', 'class' => 'collapse'));

		echo '<hr />';

		echo Form::control_group(
			Form::input('link', null, array('class'=> 'input-block-level', 'placeholder' => __('http://'))),
			null,
			Arr::get($this->errors, 'link')
		);

		echo Form::button('save', __('Add link'), array('type' => 'submit', 'class' => 'btn btn-success'));
		echo Form::csrf();

		echo Form::close();

		return ob_get_clean();
	}
}
