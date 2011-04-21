<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Route
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Route extends Kohana_Route {

	/**
	 * Return model specific route
	 *
	 * @param   Model   $model
	 * @param   string  $action
	 * @param   string  $params
	 * @param   string  $route   Defaults to model name
	 * @return  string
	 */
	public static function model(Model $model, $action = '', $params = null, $route = null) {
		return Route::url($route ? $route : Model::model_name($model), array(
			'id'     => self::model_id($model),
			'action' => $action,
			'params' => $params,
		));
	}


	/**
	 * Return model id for routing/URLs
	 *
	 * @static
	 * @param   Model  $model
	 * @return  string
	 */
	public static function model_id(Model $model) {
		return URL::title($model->id() . ' ' . $model->slug(), '-', true);
	}

}
