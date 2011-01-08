<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * After footer
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<p>
	<?php echo __('Page rendered in {execution_time} seconds, using {memory_usage} of memory, {database_queries} database queries and {included_files} files') ?><br />
	<?php echo sprintf('Copyright &copy; 2000&ndash;%d %s', date('Y'), Kohana::config('site.site_name')) ?> - <?php echo __('Powered by Anqh v:version and Kohana v:kversion', array(':version' => Anqh::VERSION, ':kversion' => Kohana::VERSION)) ?>
</p>
