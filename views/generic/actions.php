<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Actions
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php if (!empty($actions)): ?>
<div class="actions">
	<?php foreach ($actions as $action):
			if (is_array($action)):

				// Action is a link
				$attributes = $action;
				unset($attributes['link'], $attributes['text']);
				$attributes['class'] = isset($attributes['class']) ? 'action ' . $attributes['class'] : 'action';
				echo HTML::anchor($action['link'], $action['text'], $attributes);

			else:

				// Action is HTML
				echo $action;

			endif;
		endforeach; ?>

</div>
<?php endif; ?>
