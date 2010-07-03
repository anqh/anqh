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

<?php if ($user->title) echo HTML::chars(trim($user->title)) . '<br />' ?>

<?php if (Validate::url($user->picture)) echo HTML::image($user->picture, array('width' => 160)) . '<br />'; ?>

<?php echo __('Last login: :login', array(':login' => HTML::time(Date::fuzzy_span($user->last_login), $user->last_login))) ?><br />
