<?php defined('SYSPATH') or die('No direct script access.');

//-- Environment setup --------------------------------------------------------

/**
 * Set the default time zone.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/timezones
 */
date_default_timezone_set('Europe/Helsinki');

/**
 * Set the default locale.
 *
 * @see  http://kohanaframework.org/guide/using.configuration
 * @see  http://php.net/setlocale
 */
setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Enable the Kohana auto-loader.
 *
 * @see  http://kohanaframework.org/guide/using.autoloading
 * @see  http://php.net/spl_autoload_register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @see  http://php.net/spl_autoload_call
 * @see  http://php.net/manual/var.configuration.php#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

//-- Configuration and initialization -----------------------------------------

/**
 * Environment check, defaults to Kohana::DEVELOPMENT
 */
if (isset($_ENV['ENVIRONMENT'])) {
	Kohana::$environment = $_ENV['ENVIRONMENT'];
}

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 */
Kohana::init(array(
	'base_url'   => '/',
	'index_file' => false,
	'profile'    => in_array(Kohana::$environment, array(Kohana::DEVELOPMENT, Kohana::TESTING)),
	'caching'    => !in_array(Kohana::$environment, array(Kohana::DEVELOPMENT, Kohana::TESTING)),
));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Kohana_Log_File(APPPATH . 'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Kohana_Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
	'core'       => MODPATH . 'anqh',       // Anqh core
	'events'     => MODPATH . 'events',     // Anqh event calendar
	'forum'      => MODPATH . 'forum',      // Anqh forums
	'venues'     => MODPATH . 'venues',     // Anqh venues
	'blog'       => MODPATH . 'blog',       // Anqh blogs
	'galleries'  => MODPATH . 'galleries',  // Anqh galleries

	'database'   => MODPATH . 'database',   // Database access
	'jelly'      => MODPATH . 'jelly',      // Jelly ORM
	'postgresql' => MODPATH . 'postgresql', // PostgreSQL
	// 'formo'    => MODPATH . 'formo',       // Form module
	'cache'      => MODPATH . 'cache',      // Caching with multiple backends
	'pagination' => MODPATH . 'pagination', // Paging of results

	// 'auth'       => MODPATH.'auth',       // Basic authentication
	// 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
	// 'image'      => MODPATH.'image',      // Image manipulation
	// 'orm'        => MODPATH.'orm',        // Object Relationship Mapping
	// 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
));

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */
Route::set('default', '(<controller>(/<action>(/<id>)))')
	->defaults(array(
		'controller' => 'index',
		'action'     => 'index',
	));

/**
 * Execute the main request. A source of the URI can be passed, eg: $_SERVER['PATH_INFO'].
 * If no source is specified, the URI will be automatically detected.
 */
try {

	// Attempt to execute the response
	$request = Request::instance()->execute();

} catch (Exception $e) {

	// Throw errors in development environment
	if (Kohana::$environment == Kohana::DEVELOPMENT || Kohana::$environment == Kohana::TESTING) {
		throw $e;
	}

	// Log errors
	Kohana::$log->add(Kohana::ERROR, Kohana::exception_text($e));

	if ($e instanceof Kohana_Request_Exception) {

		// Annoying 404 with uris with dots, can't use Request at all
		echo __('Something fishy just happened.. please go back and try not to do this again.');
		exit;

	} else {

		// Normal 404
		$request = Request::factory('error/404')->execute();

	}
}

/**
 * Add statistics to the response.
 */
if ($request->response) {

	// Render the request to get all pending database queries and files
	$request->response = (string)$request->response;

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
	$request->response = strtr($request->response, $total);
}

/**
 * Display the request response.
 */
echo $request->send_headers()->response;
