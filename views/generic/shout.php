<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Shout
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

$ordered = array();
foreach ($shouts as $shout)
	$ordered[] = '<li>' . HTML::time(Date::format('HHMM', $shout->created), $shout->created) . ' ' . HTML::user($shout->author) . ': ' . Text::smileys(HTML::chars($shout->shout)) . '</li>';
?>

<ul>
	<?php echo implode("\n", array_reverse($ordered)) ?>
</ul>

<?php if ($can_shout): ?>

<?php echo Form::open(Route::get('shouts')->uri(array('action' => 'shout'))) ?>
<fieldset class="horizontal">
	<ul>
		<?php echo Form::input_wrap('shout', '', array('maxlength' => 300, 'title' => __('Shout')), '', $errors) ?>
		<li><?php echo Form::submit(false, __('Shout')) ?></li>
	</ul>
	<?php echo Form::csrf() ?>
</fieldset>
<?php echo Form::close() ?>

<?php

// AJAX hooks
	echo HTML::script_source('
head.ready("jquery", function() {

	$("section.shout form").live("submit", function(e) {
		e.preventDefault();
		var shouts = $(this).closest("section.shout");
		$.post($(this).attr("action"), $(this).serialize(), function(data) {
			shouts.replaceWith(data);
		});
		return false;
	});

});
');
endif;
