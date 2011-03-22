<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Request
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Request extends Kohana_Request {

	/**
	 * Redirect back to history
	 *
	 * @static
	 * @param   string   $default  if no history found
	 * @param   boolean  $return   return url, don't redirect
	 * @return  string
	 */
	public static function back($default = '/', $return = false) {
		$url = Session::instance()->get('history', $default);

		if ($return) {
			return $url;
		}

		Request::current()->redirect($url);
	}


	/**
	 * Return current unrouted URI, otherwise default action would be added
	 *
	 * @static
	 * @return  string
	 */
	public static function current_uri() {
		return $_SERVER['REQUEST_URI'];
	}


	/**
	 * Download a file to a new location. If no filename is provided,
	 * the original filename will be used, with a unique prefix added.
	 *
	 * @param   string   $filename   new filename
	 * @param   string   $directory  new directory
	 * @param   integer  $chmod      chmod mask
	 * @return  array    on success, upload style file array
	 * @return  false    on failure
	 */
	public function download($filename = null, $directory = null, $chmod = 0644) {

		// If no filename given, use remote filename with uniqid
		$original_filename = basename(parse_url($this->_uri, PHP_URL_PATH));
		if ($filename === null) {
			$filename = uniqid() . $original_filename;
		}

		// Remove spaces from the filename
		if (Upload::$remove_spaces === true) {
			$filename = preg_replace('/\s+/', '_', $filename);
		}

		// Use the pre-configured upload directory if not given
		if ($directory === null) {
			$directory = Upload::$default_directory;
		}
		if (!is_dir($directory) || !is_writable(realpath($directory))) {
			throw new Kohana_Exception('Directory :dir must be writable', array(':dir' => Debug::path($directory)));
		}

		// Make the filename into a complete path
		$filename = realpath($directory) . DIRECTORY_SEPARATOR . $filename;

		// Download file
		try {
			$response = $this->execute();

			if ($response->status() == 200) {
				$fh = fopen($filename, 'w');
				fwrite($fh, $response->body());
				$size = Arr::get(fstat($fh), 'size', 0);
				fclose($fh);
			} else {
				return false;
			}

			// Set permissions
			if ($chmod !== false) {
				chmod($filename, $chmod);
			}

			// Build file array
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime  = finfo_file($finfo, $filename);
			finfo_close($finfo);

			return array(
				'error'    => UPLOAD_ERR_OK,
				'name'     => $original_filename,
				'type'     => $mime,
				'tmp_name' => $filename,
				'size'     => $size,
			);

		} catch (Kohana_Exception $e) {
			return false;
		}
	}


	/**
	 * Get client host name
	 *
	 * @static
	 * @return  string
	 */
	public static function host_name() {
		static $host_name;

		if (!is_string($host_name)) {
			try {
				$host_name = (self::$client_ip == '0.0.0.0') ? self::$client_ip : gethostbyaddr(self::$client_ip);
			} catch (ErrorException $e) {
				$host_name = self::$client_ip;
			}
		}

		return $host_name;
	}

}
