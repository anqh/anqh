<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Hover card
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php if (Validate::url($user->picture)): ?>
<?php echo HTML::image($user->picture, array('width' => 160)) ?>
<?php endif; ?>
