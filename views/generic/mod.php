<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * View Module a.k.a. Midget
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<section<?php echo $id ? ' id="' . $id . '"' : '' ?> class="<?php echo $class ?>">
	<?php if ($title): ?>
	<header>
		<h4><?php echo $title ?></h4>
		<?php if (isset($subtitle)) echo '<span class="subtitle">' . $subtitle . '</span>' ?>
	
	</header>
	<?php endif; ?>
	<div class="container">

<?php if ($actions) echo $actions; ?>

<?php if ($pagination) echo $pagination; ?>

<?php echo $content ?>

<?php if ($pagination) echo $pagination; ?>

<?php if ($actions2) echo $actions2; ?>

	</div>
</section><!-- <?php echo $id ? '#' . $id : '.' . $class ?> -->
