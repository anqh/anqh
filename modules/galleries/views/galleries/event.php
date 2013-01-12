<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Event info
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

if (!isset($event) || !$event->loaded()):
	echo __('No event selected.');
	return;
endif;
?>

<?php	if ($event->dj): ?>
<article class="dj">
	<?php echo Text::auto_p(HTML::chars($event->dj)) ?>
</article>
<?php	endif; ?>

<?php echo HTML::anchor(Route::get('galleries')->uri(array('action' => 'upload')) . '?from=' . $event->id, __('Continue'), array('class' => 'action'));
