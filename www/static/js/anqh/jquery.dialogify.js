/**
 * Ajax dialog.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
(function ($) {

	$.fn.dialogify = function() {
		var href = this.attr('href') || this.attr('data-href');
		if (!href) {
			return false;
		}

		var title  = this.attr('data-dialog-title'),
		    width  = this.attr('data-dialog-width') || 300,
		    height = this.attr('data-dialog-height') || 'auto';
		$('<div style="display:none"></div>')
			.appendTo('body')
			.dialog({
				modal:     true,
				title:     title,
				width:     width,
				height:    height,
				closeText: '☓',
				open: function() {
					$(this).load(href);
				},
				close: function() {
					$(this).remove();
				}
			});

		return false;
	};

})(jQuery);
