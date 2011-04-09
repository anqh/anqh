<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kohana
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Kohana extends Kohana_Core {

	/**
	 * Override core shutdown_handler to user Anqh_Exceptions.
	 *
	 * @uses  Anqh_Exception::handler
	 */
	public static function shutdown_handler() {

		// Do not execute when not active
		if (!Kohana::$_init) {
			return;
		}

		try {
			if (Kohana::$caching === true and Kohana::$_files_changed === true) {

				// Write the file path cache
				Kohana::cache('Kohana::find_file()', Kohana::$_files);

			}
		}	catch (Exception $e) {

			// Pass the exception to the handler
			Anqh_Exception::handler($e);

		}

		if (Kohana::$errors and $error = error_get_last() and in_array($error['type'], Kohana::$shutdown_errors)) {

			// Clean the output buffer
			ob_get_level() and ob_clean();

			// Fake an exception for nice debugging
			Anqh_Exception::handler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));

			// Shutdown now to avoid a "death loop"
			exit(1);

		}

	}

}
