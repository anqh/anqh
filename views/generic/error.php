<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Error message
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

if (isset($title)) echo '<h1>' . $title . '</h1>';

if (isset($message)) echo $message;
