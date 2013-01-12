<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Ratings
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

$rating = $count ? $total / $count : 0;
?>
<ol class="rating">
	<?php for ($r = 1; $r <= 5; $r++): ?>
	<li><var class="icon <?php echo ($rating >= $r - .25 ? 'rate' : ($rating >= $r - .75 ? 'rate-half' : 'rate-empty')) ?>"<?php if ($rate) echo ' title="', $r, '"'; ?>></var></li>
	<?php endfor; ?>
	<?php if ($score): ?>
	<li class="total"><var title="<?php echo __($count == 1 ? ':rates rating' : ':rates ratings', array(':rates' => $count)) ?>"><?php echo Num::format($rating, 2) ?></var></li>
	<?php endif; ?>
</ol>
