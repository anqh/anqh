<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Member image
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php if ($user->default_image->id): ?>
<?php echo HTML::image($user->default_image->get_url(), array('width' => 290)) ?>
<?php elseif (Validate::url($user->picture)): ?>
<?php echo HTML::image($user->picture) ?>
<?php endif; ?>
