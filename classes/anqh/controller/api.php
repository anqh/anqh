<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh API controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_API extends Controller {

	const FORMAT_JSON = '.json';
	const FORMAT_XML  = '.xml';

	/**
	 * @var  string  Output format
	 */
	protected $format;

	/**
	 * @var  array  Available output formats
	 */
	public static $_formats = array(self::FORMAT_JSON, self::FORMAT_XML);

	/**
	 * @var  array  Data to be returned
	 */
	protected $data = array();

	/**
	 * @var  string  API version
	 */
	protected $version;

	/**
	 * @var  array  Available versions
	 */
	public static $_versions = array('v1');


	/**
	 * Construct controller
	 */
	public function before() {

		// Check version
		$this->version = $this->request->param('version');
		if (!in_array($this->version, self::$_versions)) {
			throw new Controller_API_Exception('Invalid version');
		}

		// Check format
		$this->format = $this->request->param('format', self::FORMAT_JSON);
		if (!in_array($this->format, self::$_formats)) {
			throw new Controller_API_Exception('Invalid format');
		}

		// Set result defaults
		$this->data = array(
			'version' => $this->version,
		);

		return parent::before();
	}


	public function after() {
		switch ($this->format) {

			case self::FORMAT_JSON:
		    $this->request->headers['Content-Type'] = 'application/json';
		    $this->request->response = json_encode($this->data);
		    break;

			case self::FORMAT_XML:
		    $this->request->headers['Content-Type'] = 'application/xml';
		    $this->request->response = Arr::xml($this->data);
		    break;

		}
	}

}
