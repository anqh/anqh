<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * View Mod
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<section class="<?php echo $class ?>"<?php echo $id ? ' id="' . $id . '"' : '' ?>>
	<div class="container">
		<?php if ($title): ?>
			
		<header>
			<h4><?php echo $title ?></h4>
		</header>
		<?php endif; ?>

<?php if ($pagination) echo $pagination; ?>

<?php echo $content ?>

<?php if ($pagination) echo $pagination; ?>

	</div>
</section><!-- <?php echo $class ?> -->
