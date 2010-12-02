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
			<header><?php echo HTML::anchor(
				Route::get($route)->uri(array(
					'action' => $action,
					'year'   => $years,
				)),
				$years,
				array('class' => 'year' . ($year == $years ? ' selected' : ''))) ?></header>
			<ol>

			<?php foreach ($y as $m => $count): ?>
				<li><?php echo HTML::anchor(
					Route::get($route)->uri(array(
						'action' => $action,
						'year'   => $years,
						'month'  => $m
					)),
					$m,
					array('class' => 'month' . ($year == $years && $month == $m ? ' selected' : ''))) ?> (<?= $count ?>)</li>
			<?php endforeach ?>

			</ol>
		</li>
	<?php endforeach ?>

	</ol>
</nav>
