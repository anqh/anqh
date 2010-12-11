<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Core {

	/**
	 * Anqh version
	 */
	const VERSION = 0.5;


	/**
	 * Get/set Open Graph tags
	 *
	 * @static
	 * @param   string  $key    Null to get all
	 * @param   string  $value  Null to get, false to clear
	 * @return  mixed
	 */
	public static function open_graph($key = null, $value = null) {
		static $og;

		// Initialize required Open Graph tags when setting first value
	  if ($value && !is_array($og)) {
		  if ($app_id = Kohana::config('site.facebook')) {
				$og = array(
					'og:title'     => Kohana::config('site.site_name'),
					'og:type'      => 'article',
					'og:image'     => URL::site('/ui/opengraph.jpg', true),
					'og:url'       => URL::site('', true),
					'og:site_name' => Kohana::config('site.site_name'),
					'fb:app_id'    => $app_id,
				);
		  }
	  }

	  if (!is_array($og)) {

		  // Facebook/Open Graph disabled
		  return;


	  } else if (is_null($value)) {

		  // Get
		  return is_null($key) ? $og : Arr::get($og, 'og:' . $key);

	  } else if ($value === false) {

		  // Delete
		  unset($og['og:' . $key]);

	  } else {

		  // Set
		  $og['og:' . $key] = $value;

	  }
	}

}
