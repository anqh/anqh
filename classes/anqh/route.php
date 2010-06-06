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
	 * Return model specific route, if $params is null, use $action as $params
	 *
	 * @param   Jelly_Model  $model
	 * @param   string       $action
	 * @param   string       $params
	 * @return  string
	 */
	public static function model(Jelly_Model $model, $action = null, $params = null) {
		$route  = Jelly::model_name($model);
		$id     = URL::title($model->id() . ' ' . $model->name());
		$uri    = array(
			'id'     => $id,
			'action' => $action && !is_null($params) ? $action : null,
			'params' => is_null($params) ? $action : ($params ? $params : null)
		);
		try {
			return Route::get($route)->uri($uri);
		} catch (Kohana_Exception $e) {
			return $route . '/' . $id . ($uri['action'] ? '/' . $uri['action'] : '') . ($uri['params'] ? '/' . $uri['params'] : '');
		}
	}

}
