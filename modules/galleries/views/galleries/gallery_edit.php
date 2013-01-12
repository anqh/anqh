<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Edit gallery
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

echo Form::open(null, array('onsubmit' => 'return false'));
?>

	<fieldset>
		<ul>
			<?php echo Form::input_wrap('name', $event, null, __('Event name'), $errors, __('Enter at least 3 characters')) ?>
		</ul>
	</fieldset>

<?php
echo Form::close();

// Name autocomplete
echo HTML::script_source('
head.ready("anqh", function() {
	$("#field-name").autocompleteEvent({
		"action": function(event, ui) {
			window.location = "' .  URL::site(Route::get('galleries')->uri(array('action' => 'upload'))) . '?from=" + ui.item.id;
		},
	});
	return;
	$("#field-name")
		.autocomplete({
			minLength: 3,
			source: function(request, response) {
				$.ajax({
					url: "/api/v1/events/search",
					dataType: "json",
					data: {
						q: request.term,
						limit: 25,
						filter: "past",
						search: "name",
						field: "id:name:city:stamp_begin",
						order: "stamp_begin.desc"
					},
					success: function(data) {
						response($.map(data.events, function(item) {
							return {
								label: item.name,
								stamp: item.stamp_begin,
								city: item.city,
								value: item.name,
								id: item.id
							}
						}))
					}
				});
			},
			select: function(event, ui) {
				window.location = "' .  URL::site(Route::get('galleries')->uri(array('action' => 'upload'))) . '?from=" + ui.item.id;
			},
		})
		.data("autocomplete")._renderItem = function(ul, item) {
			return $("<li></li>")
				.data("item.autocomplete", item)
				.append("<a>" + $.datepicker.formatDate("dd.mm.yy", new Date(item.stamp * 1000)) + " " + item.label + ", " + item.city + "</a>")
				.appendTo(ul);
		};
});
');
