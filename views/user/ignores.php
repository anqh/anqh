<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Ignorelist
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>
<ul>
	<?php foreach ($ignores as $ignore): ?>

	<li class="group">
		<?php echo HTML::avatar($ignore['avatar'], $ignore['username']) ?>
		<?php echo HTML::user($ignore) ?>
	</li>
	<?php endforeach; ?>

</ul>
