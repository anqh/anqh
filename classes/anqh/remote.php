<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Remote
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Remote extends Kohana_Remote {

	// Default cURL options
	public static $default_options = array(
		CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; Kohana v3.0 +http://kohanaphp.com/)',
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_TIMEOUT        => 5,
		CURLOPT_FOLLOWLOCATION => true,
	);


	/**
	 * Download a file to a new location. If no filename is provided,
	 * the original filename will be used, with a unique prefix added.
	 *
	 * @param   string   remote url
	 * @param   string   new filename
	 * @param   string   new directory
	 * @param   integer  chmod mask
	 * @return  array    on success, upload style file array
	 * @return  false    on failure
	 */
	public static function download($url, $filename = NULL, $directory = NULL, $chmod = 0644) {
		if (!Validate::url($url)) {
			return false;
		}

		// If no filename given, use remote filename with uniqid
		$original_filename = basename(parse_url($url, PHP_URL_PATH));
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
			throw new Kohana_Exception('Directory :dir must be writable', array(':dir' => Kohana::debug_path($directory)));
		}

		// Make the filename into a complete path
		$filename = realpath($directory) . DIRECTORY_SEPARATOR . $filename;

		// Download file
		try {
			$fh = fopen($filename, 'w');
			Remote::get($url, null, array(
				CURLOPT_RETURNTRANSFER => true, // Must be declared before CURLOPT_FILE
				CURLOPT_FILE           => $fh
			));
			$size = Arr::get(fstat($fh), 'size', 0);
			fclose($fh);

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
	 * Do a GET request
	 *
	 * @static
	 * @param   string  $url
	 * @param   array   $params
	 * @param   array   $options
	 * @return  string
	 *
	 * @throws  Kohana_Exception
	 */
	public static function get($url, array $params = null, array $options = null) {
		if (!empty($params)) {
			$url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params, '', '&');
		}

		return parent::get($url, $options);
	}


	/**
	 * Do a POST request
	 *
	 * @static
	 * @param   string  $url
	 * @param   array   $params
	 * @param   array   $options
	 * @return  string
	 *
	 * @throws  Kohana_Exception
	 */
	public static function post($url, array $params = null, array $options = null) {
		$options = array(CURLOPT_POST => true) + (array)$options;

		if (!empty($params)) {
			$options[CURLOPT_POSTFIELDS] = http_build_query($params);
		}

		return parent::get($url, $options);
	}

}
