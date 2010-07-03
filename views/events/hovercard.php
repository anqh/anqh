<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Hover card
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php if (Validate::url($event->flyer_front_url)) echo HTML::image($event->flyer_front_url, array('width' => 160)) . '<br />'; ?>
