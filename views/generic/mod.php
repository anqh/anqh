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
	<div class="container">
		<?php if ($title): ?>

		<header>
			<h4><?php echo $title ?></h4>
			<?php if (isset($subtitle)) echo '<span class="subtitle">' . $subtitle . '</span>' ?>
			
		</header>
		<?php endif; ?>

<?php if ($pagination) echo $pagination; ?>

<?php echo $content ?>

<?php if ($pagination) echo $pagination; ?>

	</div>
</section><!-- <?php echo $id ? '#' . $id : '.' . $class ?> -->
