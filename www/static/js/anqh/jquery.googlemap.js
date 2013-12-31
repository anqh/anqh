/**
 * Google Maps helper
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
(function ($, Anqh) {

	$.fn.googleMap = function(options) {

		// Asynchronous loading
		if (!Anqh.geocoder) {
			Anqh.geocoder = new google.maps.Geocoder();
		}

		var defaults = {
			lat:        60.1695,
			long:       24.9355,
			zoom:       14,
			mapTypeId:  google.maps.MapTypeId.ROADMAP,
			marker:     false,
			infowindow: false,
			mapTypeControlOptions: { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU }
		};

		options = $.extend(defaults, options || {});

		// Geocode address if given
		if (options.address && options.city && options.address != '' && options.city != '') {
			var geocode = options.address + ", " + options.city;
			Anqh.geocoder.geocode({ address: geocode }, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK && results.length) {
					options.lat = results[0].geometry.location.lat();
					options.long = results[0].geometry.location.lng();
					options.marker = true;
				}
			});
		}

		var center = new google.maps.LatLng(options.lat, options.long);
		Anqh.map = new google.maps.Map(this.get(0), $.extend(options, { center: center }));

		// Add marker
		if (options.marker) {
			var marker = new google.maps.Marker({
				position: center,
				map: Anqh.map,
				title: options.marker ? '' : options.marker
			});
			if (options.infowindow) {
				var infowindow = new google.maps.InfoWindow({
					content: options.infowindow
				});
				google.maps.event.addListener(marker, 'click', function() {
					infowindow.open(Anqh.map, marker);
				});
			}
		}

	};

})(jQuery, Anqh);
