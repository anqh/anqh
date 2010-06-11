<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Newsfeed
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<ul>
	<?php foreach ($newsfeed as $item): ?>

		<li class="clearfix">
			<?php echo HTML::avatar($item['user']->avatar, $item['user']->username) ?>
			<?php echo HTML::user($item['user']) ?>
			<?php echo $item['text'] ?>
			<?php echo HTML::time(Date::fuzzy_span($item['stamp']), $item['stamp']) ?>
		</li>
	<?php endforeach; ?>

</ul>
