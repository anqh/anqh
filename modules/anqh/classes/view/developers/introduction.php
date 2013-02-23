<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Developers_Introduction
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Developers_Introduction extends View_Section {

	/**
	 * Create new view.
	 */
	public function __construct() {
		parent::__construct();
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

<h2 id="introduction">Introduction</h2>

<p>
	Welcome to the ridiculously short developer documentation for klubitus!
</p>

<h2 id="anqh">Anqh</h2>

<p>
	Klubitus is an <em>almost</em> vanilla version of <?= HTML::anchor('http://github.com/anqh/anqh', 'Anqh ' . Anqh::VERSION, array('class' => 'label label-info')) ?>,
	an open source <?= HTML::anchor('http://php.net', 'PHP 5.3', array('class' => 'label label-info')) ?> project built on top of <?= HTML::anchor('http://kohanaframework.org', 'Kohana ' . Kohana::VERSION, array('class' => 'label label-info')) ?>.
</p>

<?php

		return ob_get_clean();
	}

}
