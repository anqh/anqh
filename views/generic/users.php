<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * User list
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>


<div class="users">
	<?php

		// Build short (friends) and long (others) user list
		$short = $long = array();
		foreach ($users as $user):
			$user = is_array($user) ? $user : Model_User::find_user_light($user);
			if ($viewer && $viewer->is_friend($user)):
				$short[mb_strtoupper($user['username'])] = HTML::user($user);
			else:
				$long[mb_strtoupper($user['username'])] = HTML::user($user);
			endif;
		endforeach;
		ksort($long);

		// If no friends, pick random from long
		if (empty($short) && !empty($long)):
			$shorts = (array)array_rand($long, min(10, count($long)));
			foreach ($shorts as $move):
				$short[$move] = $long[$move];
				unset($long[$move]);
			endforeach;
		endif;
		ksort($short);

	?>

	<?php if (count($short)) echo implode(', ', $short) ?>

	<?php if (count($long)): ?>
	<?php echo __('and'), ' ', HTML::anchor(
		'#users',
		__(count($long) == 1 ? ':count other' : ':count others', array(':count' => count($long))),
		array('class' => 'expander', 'title' => __('Show all'), 'onclick' => '$(".users .long").toggle("fast"); return false;')
	) ?>
	<div class="long">
	<?php echo implode(', ', $long) ?>
	</div>
	<?php endif; ?>

</div>
