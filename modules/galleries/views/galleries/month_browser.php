<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Month browser
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<nav>
	<ol>

	<?php foreach ($months as $years => $y): ?>
		<li>
			<h4><?php echo HTML::anchor(
				Route::get($route)->uri(array(
					'action' => $action,
					'year'   => $years,
				)),
				$years == 1970 ? __('Unknown') : $years,
				array('class' => 'year' . ($year == $years ? ' selected' : ''))) ?></h4>
			<ol>

			<?php foreach ($y as $m => $count): ?>
				<li><?php echo HTML::anchor(
					Route::get($route)->uri(array(
						'action' => $action,
						'year'   => $years,
						'month'  => $m
					)),
					$m > 0 ? strftime('%b', strtotime("$years-$m-1")) : '???',
					array('class' => 'month' . ($year == $years && $month == $m ? ' selected' : ''))) ?> (<?= $count ?>)</li>
			<?php endforeach ?>

			</ol>
		</li>
	<?php endforeach ?>

	</ol>
</nav>
