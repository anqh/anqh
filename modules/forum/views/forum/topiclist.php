<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Topic list
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php	if (empty($topics)): ?>
<span class="notice"><?php echo __('No topics found') ?></span>
<?php else: ?>
<ul>

	<?php foreach ($topics as $topic): ?>
	<li>
		<?php echo HTML::anchor(Route::model($topic, '?page=last#last'), HTML::chars($topic->name), array('title' => '[' . Date::short_span($topic->last_posted, false) . '] ' . $topic->name)) ?>
	</li>
	<?php endforeach; ?>

</ul>
<?php	endif; ?>
