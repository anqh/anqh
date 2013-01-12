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
if ($count = count($online)) {
	$counts[] = __($count == 1 ? ':members member' : ':members members', array(':members' => $count));
}
if ($guests) {
	$counts[] = __($guests == 1 ? ':guests guest':  ':guests guests', array(':guests' => $guests));
}

echo '<div class="totals">' . __(':users users online', array(
	':users' => '<var title="' . implode(', ', $counts) . '">' . (count($online) + $guests) . '</var>'
)) . '</div>';

echo View::factory('generic/users', array(
	'viewer'    => $viewer,
	'users'     => $online,
));
