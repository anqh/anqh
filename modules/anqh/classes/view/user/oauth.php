<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Sign in with existing non-paired OAuth user
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_User_OAuth extends View_Section {

	/**
	 * @var  array
	 */
	public $external_user;

	/**
	 * @var  Model_User
	 */
	public $user;


	/**
	 * Create new view.
	 *
	 * @param  Model_User  $user
	 * @param  array       $external_user
	 */
	public function __construct(Model_User $user, array $external_user) {
		parent::__construct();

		$this->user          = $user;
		$this->external_user = $external_user;

		$this->title = __('Sign in with') . ' <i class="icon-facebook"></i>';
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

<div class="alert alert-info">
	<?= __("You haven't connected your accounts using :email yet. Please enter your password for :username and we will do the connecting for you!", array(
			':email'    => '<strong>' . $this->external_user['email'] . '</strong>',
			':username' => '<strong>' . HTML::chars($this->user->username) . '</strong>'
	)) ?>
</div>

<div class="row">

	<br />

	<div class="media span3">
		<?= HTML::avatar('https://graph.facebook.com/' . $this->external_user['id'] . '/picture', null, 'pull-left facebook') ?>
		<div class="media-body">
			<?= HTML::anchor($this->external_user['link'], HTML::chars($this->external_user['name']), array('target' => '_blank')) ?>
		</div>
	</div>

	<div class="span1">
		<br />
		<i class="text-info icon-link"></i>
	</div>

	<div class="media span3">
		<?= HTML::avatar($this->user->avatar, $this->user->username, 'pull-left') ?>
		<div class="media-body">
			<?= HTML::user($this->user) ?>
		</div>
	</div>

</div>

<br />

<div class="form-actions">

	<?= Form::open(Route::url('sign', array('action' => 'in')), array('class' => 'form-inline')) ?>

	<div class="input-append">
		<?= Form::password('password', null, array('class' => 'input-small', 'placeholder' => __('Password'))) ?>
		<?= Form::button(null, __('Sign in') . ' <i class="icon-signin"></i>', array('class' => 'btn btn-primary', 'title' => __('Sign in and connect accounts'))) ?>
	</div>

	<?= Form::hidden('username', $this->user->username) ?>
	<?= Form::hidden('external', 'facebook') ?>
	<?= Form::hidden('remember', 'true') ?>
	<?= Form::close(); ?>

</div>

<?php

		return ob_get_clean();
	}

}
