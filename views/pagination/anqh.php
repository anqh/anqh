<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Pagination
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

// Build page number array
if ($total_pages < 13):

	// Show all pages
	$pages = range(1, $total_pages);

else:

	// Add start and end
	$pages = array(1, $total_pages);
	if ($current_page < $total_pages / 2):
		$pages[] = 2;
		$pages[] = 3;
	else:
		$pages[] = $total_pages - 1;
		$pages[] = $total_pages - 2;
	endif;
	$pages = array_merge($pages, range(max(1, $current_page - 2), min($total_pages, $current_page + 2)));

	// Add halves if useful
	$first_half = ceil($current_page / 2);
	$last_half = ceil($total_pages - ($total_pages - $current_page) / 2);
	if ($first_half > 5 && $current_page - $first_half > 7) $pages[] = $first_half;
	if ($total_pages - $last_half > 5 && $last_half - $current_page > 7) $pages[] = $last_half;
endif;
sort($pages);
$pages = array_unique($pages);
$previous = 1;
?>

<p class="pagination">

	<?php if ($previous_page): ?>
		<?php echo HTML::anchor($page->url($previous_page), '&laquo;&nbsp;') ?>
	<?php else: ?>
		&laquo;&nbsp;
	<?php endif ?>

	<?php foreach ($pages as $i): ?>
		<?php if ($i - $previous > 1): ?>
			&hellip;
		<?php endif; $previous = $i; ?>
		<?php if ($i == $current_page): ?>
			<strong><?php echo $i ?></strong>
		<?php else: ?>
	<?php echo HTML::anchor($page->url($i), $i) ?>
		<?php endif ?>
	<?php endforeach ?>

	<?php if ($next_page): ?>
		<?php echo HTML::anchor($page->url($next_page), '&nbsp;&raquo;'); ?>
	<?php else: ?>
		&nbsp;&raquo;
	<?php endif ?>

</p>
