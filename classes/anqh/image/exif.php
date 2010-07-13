<?php defined('SYSPATH') or die('No direct access allowed.');

require_once(Kohana::find_file('vendor', 'exif/exif'));

/**
 * EXIF class
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Image_EXIF {

	/**
	 * Parsed exif data
	 *
	 * @var  array
	 */
	public $exif = array();

	/**
	 * Unparsed exif data
	 *
	 * @var  array
	 */
	public $exif_raw = array();

	/**
	 * Parse only these variables
	 *
	 * @var  array
	 */
	public $exif_vars = array(
		// db field     => array('tree',   'exif key')
		'make'          => array('IFD0',   'Make'),                // Camera maker
		'model'         => array('IFD0',   'Model'),               // Camera model
		'exposure'      => array('SubIFD', 'ExposureTime'),        // Shutter speed
		'program'       => array('SubIFD', 'ExposureProgram'),     // Exposure program
		'aperture'      => array('SubIFD', 'FNumber'),             // Aperture
		'focal'         => array('SubIFD', 'FocalLength'),         // Focal length
		'iso'           => array('SubIFD', 'ISOSpeedRatings'),     // ISO sensitivity
		'taken'         => array('SubIFD', 'DateTimeOriginal'),    // Time taken
		'metering'      => array('SubIFD', 'MeteringMode'),        // Metering mode
	  'flash'         => array('SubIFD', 'Flash'),               // Flash fired
		'width'         => array('SubIFD', 'ExifImageWidth'),      // Original width
		'height'        => array('SubIFD', 'ExifImageHeight'),     // Original height
		'latitude' 	    => array('GPS',    'Latitude'),            // Latitude
		'latitude_ref'  => array('GPS',    'Latitude Reference'),  // Latitude reference
		'longitude'     => array('GPS',    'Longitude'),           // Longitude
    'longitude_ref' => array('GPS',    'Longitude Reference'), // Longitude reference
		'altitude'      => array('GPS',    'Altitude'),            // Altitude
		'altitude_ref'  => array('GPS',    'Altitude Reference'),  // Altitude reference
		'lens'          => array('AUX',    'Lens'),                // Lens information
	);

	/**
	 * EXIF image filename
	 *
	 * @var  string
	 */
	public $filename;


	/**
	 * Create new EXIF object and initialize our own settings
	 *
	 * @param  string  $filename
	 */
	public function __construct($filename) {
		if (!empty($filename)) {

			// does the file exists
			if (!is_file($filename)) {
				throw new Kohana_Exception('Image not found: :file', array(':file' => $filename));
			}

			// is it readable
			if (!is_readable($filename)) {
				throw new Kohana_Exception('Image not readable: :file', array(':file' => $filename));
			}

			$this->filename = $filename;
		}
	}


	/**
	 * Get EXIF property
	 *
	 * @param   string  $property
	 * @return  mixed
	 */
	public function __get($property) {
		return Arr::get($this->exif, $property);
	}


	/**
	 * Creates and returns new Exif object
	 *
	 * @param   string  $filename
	 * @return  Exif
	 */
	public static function factory($filename) {
		return new Image_Exif($filename);
	}


	/**
	 * Read EXIF data from file
	 *
	 * @return  bool
	 */
	public function read() {
		$exif = array();

		// Read raw EXIF data
		$exif_raw = read_exif_data_raw($this->filename, false);
		$this->exif_raw = $exif_raw;
		if (isset($exif_raw['ValidEXIFData'])) {

			// parse only wanted data
			foreach ($this->exif_vars as $field => $exif_var) {
				if (isset($exif_raw[$exif_var[0]][$exif_var[1]]) && !empty($exif_raw[$exif_var[0]][$exif_var[1]]))
					$exif[$field] = $exif_raw[$exif_var[0]][$exif_var[1]];
			}

		}

		$this->exif = $exif;
		return $exif;
	}

}
