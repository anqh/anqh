<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Roles list
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<ul>
	<?php foreach ($roles as $role): ?>
	<li><?php echo HTML::anchor(Route::model($role, 'edit', false), $role->name), ' - ', HTML::chars($role->description) ?></li>
	<?php endforeach; ?>
</ul>
