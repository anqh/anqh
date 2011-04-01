<?php defined('SYSPATH') or die('No direct script access.');

//-- Environment setup --------------------------------------------------------

// Load the core Kohana class
require SYSPATH . 'classes/kohana/core' . EXT;

if (is_file(APPPATH . 'classes/kohana' . EXT)) {

	// Application extends the core
	require APPPATH . 'classes/kohana' . EXT;

} else {

	// Load empty core extension
	require SYSPATH . 'classes/kohana' . EXT;

}

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
 * Set the default language
 */
I18n::lang('en-us');

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 */
if (getenv('KOHANA_ENV') !== false) {
	Kohana::$environment = constant('Kohana::' . strtoupper(getenv('KOHANA_ENV')));
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
	'profile'    => true, //in_array(Kohana::$environment, array(Kohana::DEVELOPMENT, Kohana::TESTING)),
	'caching'    => !in_array(Kohana::$environment, array(Kohana::DEVELOPMENT, Kohana::TESTING)),
));

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Log_File(APPPATH . 'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Config_File);

/**
 * Set session handler
 */
Session::$default = 'database';

/**
 * Set cookie salt
 */
Cookie::$salt = 'anqh';

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
	'core'         => MODPATH . 'anqh',         // Anqh core
	'events'       => MODPATH . 'events',       // Anqh event calendar
	'forum'        => MODPATH . 'forum',        // Anqh forums
	'venues'       => MODPATH . 'venues',       // Anqh venues
	'blog'         => MODPATH . 'blog',         // Anqh blogs
	'galleries'    => MODPATH . 'galleries',    // Anqh galleries

	'database'     => MODPATH . 'database',     // Database access
	//'jelly'        => MODPATH . 'jelly',        // Jelly ORM
	'auto-modeler' => MODPATH . 'auto-modeler', // Auto Modeler
	'postgresql'   => MODPATH . 'postgresql',   // PostgreSQL
	'cache'        => MODPATH . 'cache',        // Caching with multiple backends
	'image'        => MODPATH . 'image',        // Image manipulation
	'pagination'   => MODPATH . 'pagination',   // Paging of results
	'email'        => MODPATH . 'email',        // Email module

	// 'auth'       => MODPATH.'auth',       // Basic authentication
	// 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
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
