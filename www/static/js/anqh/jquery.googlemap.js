/**
 * Google Maps helper
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
(function ($) {

	$.fn.googleMap = function(options) {
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
		if (options.city) {
			var geocoder = new google.maps.Geocoder()
			  , geocode  = (options.address ? options.address + ", " : '') + options.city;
			geocoder.geocode({ address: geocode }, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK && results.length) {
					options.lat    = results[0].geometry.location.lat();
					options.long   = results[0].geometry.location.lng();
					options.marker = true;
				}
			});
		}

		var center = new google.maps.LatLng(options.lat, options.long)
		  , map    = new google.maps.Map(this.get(0), $.extend(options, { center: center }));

		// Add marker
		if (options.marker) {
			var marker = new google.maps.Marker({
				position: center,
				map:      map,
				title:    options.marker ? '' : options.marker
			});
			if (options.infowindow) {
				var infowindow = new google.maps.InfoWindow({
					content: options.infowindow
				});
				google.maps.event.addListener(marker, 'click', function() {
					infowindow.open(map, marker);
				});
			}
		}

	};

})(jQuery);
