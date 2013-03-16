<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh exception handler
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Exception extends Kohana_Kohana_Exception {

	/**
	 * Exception handler.
	 *
	 * @static
	 * @param   Exception $e
	 * @return  boolean
	 */
	public static function handler(Exception $e) {

		// Development environment shows all exceptions
		if (Kohana::$environment === Kohana::DEVELOPMENT) {
			return parent::handler($e);
		}

		try {

			// Log errors
			Kohana::$log->add(Log::ERROR, self::text($e));

			// Figure out error page attributes
			$params = array(
				'action'  => 500,
				'message' => rawurlencode($e->getMessage())
			);
			if ($e instanceof HTTP_Exception) {

				// Different errors pages for different HTTP errors
				$params['action'] = $e->getCode();

			} else if ($e instanceof ReflectionException) {

				// This really shouldn't happen, ever
				$params['action'] = 404;

			} else if ($e instanceof Controller_API_Exception) {

				// API error
				$params['action'] = 403;

			}

			// Display error page
			echo Request::factory(Route::url('error', $params))
				->execute()
				->send_headers()
				->body();

		} catch (Exception $e) {

			// Clean buffers
			ob_get_level() and ob_clean();

			// Display exception
			echo parent::text($e);

			// Exit with error
			exit(1);

		}
	}


	/**
	 * Get a single line of text representing the exception:
	 *
	 * Error [ Code ]: Message ~ File [ Line ] (#id: username, ip: IP, uri: URI)
	 *
	 * @param   Exception   $e
	 * @return  string
	 */
	public static function text(Exception $e) {
		if ($user = Visitor::instance()->get_user()) {
			$user_id  = $user->id;
			$username = Text::clean($user->username);
		} else {
			$user_id  = 0;
			$username = '';
		}

		return sprintf('%s [ %s ]: %s ~ %s [ %d ] (#%d: %s, ip: %s, uri: %s)',
			get_class($e), $e->getCode(), strip_tags($e->getMessage()), Debug::path($e->getFile()), $e->getLine(), $user_id, $username, Request::$client_ip, Text::clean(Request::current_uri()));
	}

}
