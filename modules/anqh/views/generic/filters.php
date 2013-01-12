<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Filters
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

echo Form::open(null, array('class' => 'filters pills'));
?>

	<?php foreach ($filters as $type => $filter): ?>
	<fieldset>
		<!-- <legend><?php echo HTML::chars($filter['name']) ?>:</legend>-->
		<ul>
			<li>
				<?php echo Form::checkbox('filter[]', 'all', true, array('id' => 'all-' . $type)) ?>
				<?php echo Form::label('all-' . $type, __('All')) ?>
			</li>
			<?php foreach ($filter['filters'] as $key => $name): ?>
			<li>
				<?php echo Form::checkbox('filter[]', $type . '-' . $key, false, array('id' => $type . '-' . $key)) ?>
				<?php echo Form::label($type . '-' . $key, $name) ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
	<?php endforeach ?>

<?php
echo form::close();

Widget::add('footer', html::script_source('
function filters(all) {
	if (all) {

		// Open all
		$("form.filters input").each(function() {
			$("." + this.id + ":hidden").slideDown("normal");
		});

	} else {

		// Filter individually
		$("form.filters input").each(function() {
			if ($(this).is(":checked")) {
				$("." + this.id + ":hidden").slideDown("normal");
			} else {
				$("." + this.id + ":visible").slideUp("normal");
			}
		});

	}
}

head.ready("jquery-ui", function() {

	// Hook clicks
	$("form.filters :checkbox").click(function() {

		var checked = $(this).is(":checked");

		if ($(this).val() != "all") {

			// Individual filters
			if (checked) {

				// Uncheck "all"
				$("form.filters input[value=all]").attr("checked", false);

			}

			// Check "all" if no other filters
			if ($("form.filters input[value!=all]").is(":checked") == false) {
				$("form.filters input[value=all]").attr("checked", "checked");
				filters(true);
			} else {
				filters();
			}

		} else {

			// All filters
			if (!checked) {
				return false;
			}

			$("form.filters input[value!=all]").attr("checked", false);
			filters(checked);

		}

	});
});
'));
