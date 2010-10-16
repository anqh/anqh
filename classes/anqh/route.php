<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Route
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Route extends Kohana_Route {

	/**
	 * Return model specific route
	 *
	 * @param   Jelly_Model  $model
	 * @param   string       $action
	 * @param   string       $params
	 * @param   string       $route   Defaults to model name
	 * @return  string
	 */
	public static function model(Jelly_Model $model, $action = '', $params = null, $route = null) {
		return Route::get($route ? $route : Jelly::model_name($model))->uri(array(
			'id'     => self::model_id($model),
			'action' => $action,
			'params' => $params,
		));
	}


	/**
	 * Return model id for routing/URLs
	 *
	 * @static
	 * @param   Jelly_Model  $model
	 * @return  string
	 */
	public static function model_id(Jelly_Model $model) {
		return URL::title($model->id() . ' ' . $model->name(), '-', true);
	}

}
