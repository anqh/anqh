<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Users_Ignores
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Users_Ignores extends View_Section {

	/**
	 * @var  Model_User
	 */
	public $user;


	/**
	 * Create new view.
	 *
	 * @param  Model_User  $user
	 */
	public function __construct($user = null) {
		parent::__construct();

		$this->title = __('Ignores');
		$this->user  = $user;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		$ignores = array();
	  foreach ($this->user->find_ignores() as $ignore_id) {
		  $ignore = Model_User::find_user_light($ignore_id);
		  $ignores[$ignores['username']] = $ignore;
	  }
	  ksort($ignores, SORT_LOCALE_STRING);


?>

<ul class="unstyled">
	<?php foreach ($ignores as $ignore): ?>

	<li class="row-fluid">
		<?= HTML::avatar($ignore['avatar'], $ignore['username']) ?>
		<?= HTML::user($ignore) ?><br />
		<?= HTML::anchor(
			URL::user($ignore, 'unignore') . '?token=' . Security::csrf(),
			'<i class="icon-ban-circle"></i> ' . __('Unignore'),
			array('class' => 'btn btn-small ignore-delete')
		) ?>
	</li>
	<?php endforeach; ?>

</ul>


<?php

		return ob_get_clean();
	}

}
