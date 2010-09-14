<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Event
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php	if ($event->dj): ?>
<article class="dj">
	<header>
		<h3><?php echo __('Line-up') ?></h3>
	</header>
	<?php echo Text::auto_p(HTML::chars($event->dj)) ?>

</article>
<?php	endif; ?>

<?php if ($event->info): ?>
<article class="extra-info">
	<header>
		<h3><?php echo __('Extra info') ?></h3>
	</header>

	<?php echo BB::factory($event->info)->render() ?>

</article>
<?php endif; ?>
