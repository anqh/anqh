<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Geo Timezone model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Geo_Timezone extends Jelly_Model {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id'  => new Jelly_Field_Primary,
			'gmt' => new Jelly_Field_Float,
			'dst' => new Jelly_Field_Float,
		));
	}

}
