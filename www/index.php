<?php

/**
 * The directory in which your application specific resources are located.
 * The application directory must contain the bootstrap.php file.
 *
 * @see  http://kohanaframework.org/guide/about.install#application
 */
$application = '../application';

/**
 * The directory in which your modules are located.
 *
 * @see  http://kohanaframework.org/guide/about.install#modules
 */
$modules = '../modules';

/**
 * The directory in which the Kohana resources are located. The system
 * directory must contain the classes/kohana.php file.
 *
 * @see  http://kohanaframework.org/guide/about.install#system
 */
$system = '../system';

/**
 * The default extension of resource files. If you change this, all resources
 * must be renamed to use the new extension.
 *
 * @see  http://kohanaframework.org/guide/about.install#ext
 */
define('EXT', '.php');

/**
 * Set the PHP error reporting level. If you set this in php.ini, you remove this.
 * @see  http://php.net/error_reporting
 *
 * When developing your application, it is highly recommended to enable notices
 * and strict warnings. Enable them by using: E_ALL | E_STRICT
 *
 * In a production environment, it is safe to ignore notices and strict warnings.
 * Disable them by using: E_ALL ^ E_NOTICE
 *
 * When using a legacy application with PHP >= 5.3, it is recommended to disable
 * deprecated notices. Disable with: E_ALL & ~E_DEPRECATED
 */
error_reporting((int)getenv('KOHANA_ENV') > 1 ? E_ALL | E_STRICT : E_ALL ^ E_NOTICE);

/**
 * End of standard configuration! Changing any of the code below should only be
 * attempted by those with a working knowledge of Kohana internals.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 */

// Set the full path to the docroot
define('DOCROOT', realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

// Make the application relative to the docroot
if (!is_dir($application) AND is_dir(DOCROOT . $application))
	$application = DOCROOT . $application;

// Make the modules relative to the docroot
if (!is_dir($modules) AND is_dir(DOCROOT . $modules))
	$modules = DOCROOT . $modules;

// Make the system relative to the docroot
if (!is_dir($system) AND is_dir(DOCROOT . $system))
	$system = DOCROOT . $system;

// Define the absolute paths for configured directories
define('APPPATH', realpath($application) . DIRECTORY_SEPARATOR);
define('MODPATH', realpath($modules) . DIRECTORY_SEPARATOR);
define('SYSPATH', realpath($system) . DIRECTORY_SEPARATOR);

// Clean up the configuration vars
unset($application, $modules, $system);

// Load the installation check?
if (file_exists('install' . EXT)) {
	return include 'install' . EXT;
}

/**
 * Define the start time of the application, used for profiling.
 */
if (!defined('KOHANA_START_TIME')) {
	define('KOHANA_START_TIME', microtime(true));
}

/**
 * Define the memory usage at the start of the application, used for profiling.
 */
if (!defined('KOHANA_START_MEMORY')) {
	define('KOHANA_START_MEMORY', memory_get_usage());
}

// Bootstrap the application
require APPPATH . 'bootstrap' . EXT;

/**
 * Execute the main request. A source of the URI can be passed, eg: $_SERVER['PATH_INFO'].
 * If no source is specified, the URI will be automatically detected.
 */
try {

	// Attempt to execute the response
	$response = Request::factory()->execute();

} catch (Exception $e) {

	// Throw errors in development environment
	if (Kohana::$environment == Kohana::DEVELOPMENT || Kohana::$environment == Kohana::TESTING) {
		throw $e;
	}

	// Log errors
	Kohana::$log->add(Log::ERROR, Kohana_Exception::text($e));

	if ($e instanceof Kohana_Request_Exception) {

		// Annoying 404 with uris with dots, can't use Request at all
		echo __('Something fishy just happened.. please go back and try not to do this again.');
		exit;

	} else {

		// Normal 404
		$response = Request::factory('error/404')->execute();

	}
}

/**
 * Add statistics to the response.
 */
if ($response->send_headers()->body()) {

	// Render the request to get all pending database queries and files
	//$request->response = (string)$request->response;

	$queries = 0;
	if (Kohana::$profiling) {

		// DB queries
		foreach (Profiler::groups() as $group => $benchmarks) {
			if (strpos($group, 'database') === 0) {
				$queries += count($benchmarks);
			}
		}

	}

	$total = array(
		'{memory_usage}'     => number_format((memory_get_peak_usage() - KOHANA_START_MEMORY) / 1024, 2) . 'KB',
		'{execution_time}'   => number_format(microtime(true) - KOHANA_START_TIME, 5),
		'{database_queries}' => $queries,
		'{included_files}'   => count(get_included_files()),
		'{kohana_version}'   => Kohana::VERSION,
	);
	$response->body(strtr($response->body(), $total));
}

/**
 * Display the request response.
 */
echo $response->body();
