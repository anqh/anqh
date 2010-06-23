<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Setting controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Set extends Controller {

	/**
	 * Change language
	 */
	public function action_lang() {
		$language = $this->request->param('value');

		$locale = Kohana::config('locale');
		if (isset($locale['locales'][$language])) {
			Session::instance()->set('language', $locale['locales'][$language]['language'][0]);
		}

		Request::back();
	}


	/**
	 * Set page main content position
	 */
	public function action_main() {
		$position = $this->request->param('value');

		Session::instance()->set('page_main', $position == 'right' ? 'right' : 'left');

		if ($this->ajax) {
			return;
		}

		Request::back();
	}


	/**
	 * Set page width
	 */
	public function action_width() {
		$width = $this->request->param('value');

		Session::instance()->set('page_width', $width == 'wide' ? 'liquid' : 'fixed');

		if ($this->ajax) {
			return;
		}

		Request::back();
	}

}
