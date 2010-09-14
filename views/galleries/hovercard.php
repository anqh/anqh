<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Hover card
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php echo HTML::image($image->get_url('thumbnail')) ?>

<?php if ($image->description): ?>
	<?php $names = array(); foreach (explode(',', $image->description) as $name) $names[] = HTML::user(trim($name)); ?>
	<?php echo __('In picture: :users', array(':users' => implode(', ', $names))) ?>
<?php endif; ?>

<?php if ($image->author->id)
	echo '<br />&copy; ', HTML::user($image->author); ?>
