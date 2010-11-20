<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Reply
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<section class="author grid2 first">
	<?php echo HTML::avatar($user->avatar, $user->username) ?>

	<?php echo HTML::user($user) ?>
</section>

<section class="post-edit grid6">
	<?php echo View::factory('form/anqh', array('form' => $form)) ?>
</section>
