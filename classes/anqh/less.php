<?php defined('SYSPATH') or die('No direct access allowed.');

require_once(Kohana::find_file('vendor', 'lessphp/lessc.inc'));

/**
 * lessphp for Anqh
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Less extends lessc {

	/**
	 * Creates a stylesheet link with LESS support
	 *
	 * @param   string  $style       file name
	 * @param   array   $attributes  default attributes
	 * @param   bool    $index       include the index page
	 * @param   array   $imports     compare file date for these too, CSS and LESS in style @import
	 * @return  string
	 */
	public static function style($file, array $attributes = null, $index = false, $imports = null) {
		$imports  = (array)$imports;

		// Compile only .less files
		if (substr_compare($file, '.less', -5, 5, false) === 0) {
			$css = substr_replace($file, 'css', -4);
			$compiled = is_file($css) ? filemtime($css) : 0;
			try {

				// Check if imported files have changed
				$compile = filemtime($file) > $compiled;
				if (!$compile && !empty($imports)) {
					foreach ($imports as $import) {
						if (filemtime($import) > $compiled) {
							$compile = true;
							break;
						}
					}
				}

				// Compile LESS
				if ($compile) {
					$compiler = new Less($file);
					file_put_contents($css, $compiler->parse());
				}
				$file = $css;

			} catch (Exception $e) {
				Kohana::$log->add(Log::ERROR, __METHOD__ . ': Error compiling LESS file ' . $file . ', ' . $e->getMessage());
			}

		}

		return HTML::style($file . '?' . filemtime($file), $attributes, $index);
	}

}
