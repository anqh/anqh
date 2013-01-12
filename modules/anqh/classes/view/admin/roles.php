<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Admin_Roles
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Admin_Roles extends View_Section {

	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

<ul>
		<?php foreach (Model_Role::factory()->find_all() as $role): ?>
		<li><?php echo HTML::anchor(Route::model($role, 'edit', false), $role->name), ' - ', HTML::chars($role->description) ?></li>
		<?php endforeach; ?>
</ul>

<?php

		return ob_get_clean();
	}

}
