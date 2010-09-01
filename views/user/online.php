<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Online users
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

$guests = Model_User_Online::get_guest_count();
$online = Model_User_Online::find_online_users();
$counts = array();
if (count($online)) {
	$counts[] = __2(':members member', ':members members', count($online), array(':members' => count($online)));
}
if ($guests) {
	$counts[] = __2(':guests guest', ':guests guests', $guests, array(':guests' => $guests));
}

echo '<div class="totals">' . __(':users users online', array(
	':users' => '<var title="' . implode(', ', $counts) . '">' . (count($online) + $guests) . '</var>'
)) . '</div>';

echo View::factory('generic/users', array(
	'viewer'    => $viewer,
	'users'     => $online,
));
