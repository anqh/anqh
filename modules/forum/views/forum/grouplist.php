<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Group list
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<header>
	<h4><?php echo $title ?></h4>
</header>

<ul class="forum">
<?php	foreach ($groups as $group): ?>

	<li class="group">
		<h5><?php echo HTML::anchor(Route::model($group), HTML::chars($group->name)) ?></h5>
		<ul class="areas">

		<?php	foreach ($group->areas() as $area): ?>
			<?php if (Permission::has($area, Permission_Interface::PERMISSION_READ, $user)): ?>
			<li><?php echo HTML::anchor(Route::model($area), HTML::chars($area->name), array('title' => strip_tags($area->description))) ?></li>
			<?php endif; ?>
		<?php endforeach; ?>

		</ul>
	</li>

<?php endforeach; ?>
</ul>
