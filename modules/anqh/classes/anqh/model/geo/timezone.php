<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Geo Timezone model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Geo_Timezone extends AutoModeler {

	protected $_table_name = 'geo_timezones';

	protected $_data = array(
		'id'  => null,
		'gmt' => null,
		'dst' => null,
	);

}
