<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Index controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Controller_Index extends Controller_Template {

	/**
	 * Index id is home
	 *
	 * @var  string
	 */
	protected $page_id = 'home';

	/**
	 * Controller default action
	 */
	public function action_index() {

	}

}
