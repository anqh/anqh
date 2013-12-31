/**
 * Ajaxified requests.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
(function ($) {

	$.fn.ajaxify = function(url, data, type, success) {
		var $target = $(this);

		type = (type == 'post' || type == 'POST') ? 'POST' : 'GET';
		$.ajax({
			type:    type,
			url:     url,
			data:    data,
			timeout: 2500,
			success: function(data) {
				$target.slideUp('fast', function _replace() {
					$target.replaceWith(data).slideDown('fast');
				});

				if (typeof success == 'function') {
					success(data);
				}
			},
			error: function(req, err) {
				if (err === 'error') {
					err = req.statusText;
				}
				alert('Fail: ' + err);
				$target.loading(true);
			},
			beforeSend: function() {
				$target.loading();
			}
		});

		return this;
	};

})(jQuery);
