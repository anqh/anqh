<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Notifications
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Notifications extends View_Section {

	/**
	 * @var  array
	 */
	public $notifications;


	/**
	 * Create new view.
	 *
	 * @param  array  $notifications
	 */
	public function __construct(array $notifications = null) {
		parent::__construct();

		$this->notifications = $notifications;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		if (!count($this->notifications)):
			echo new View_Alert(__('No new notifications.'), null, View_Alert::INFO);
		else:

?>

<ul class="list-unstyled">

	<?php foreach ($this->notifications as $id => $notification): ?>
	<li><?= HTML::anchor(Route::url('notifications') . '?dismiss=' . $id, '&times;', array('class' => 'close notification')) ?><?= $notification ?></li>
	<?php endforeach; ?>

</ul>

<?php

		endif;

		return ob_get_clean();
	}

}
