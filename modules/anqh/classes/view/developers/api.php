<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Developers_API
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Developers_API extends View_Section {

	/**
	 * Create new view.
	 */
	public function __construct() {
		parent::__construct();
	}


	/**
	 * Build a parameter value field from array.
	 *
	 * @param   array  $fields
	 * @return  string
	 */
	public static function code_array(array $fields) {
		sort($fields);

		return '<code>' . implode("</code><br />\n<code>", $fields) . '</code>';
	}

	/**
	 * Print document table.
	 *
	 * @param   array  $parameters
	 * @return  string
	 */
	public static function parameter_table(array $parameters) {
		ob_start();

?>

<table class="table table-bordered">
	<thead>
		<tr>
			<th class="span1">Parameter</th>
			<th class="span2">Values</th>
			<th class="span5">Description</th>
		</tr>
	</thead>
	<tbody>

		<?php foreach ($parameters as $parameter => $document): ?>
		<tr>
			<td><code><?= $parameter ?></code></td>
			<td><?= $document[0] ?></td>
			<td><?= $document[1] ?></td>
		</tr>
		<?php endforeach ?>

	</tbody>
</table>

<?php

		return ob_get_clean();
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

<h2 id="api">API</h2>

<h3 id="api-overview">Overview</h3>

<p>
	You can fetch public read-only data with our <strong>REST API</strong> using simple <strong>HTTP GET requests</strong>.
	The response is formatted in <strong>JSON</strong>.
	<!--Response can be formatted in either <strong>JSON</strong> (recommended) or <strong>XML</strong>.<br />-->
	Parameters taking multiple values are separated with a colon <code>:</code>
</p>

<p>
	API base URL:
		<pre>http://api.klubitus.org/v1</pre>
	<!--or
		<pre>https://api.klubitus.org/v1</pre>-->
	<!--Use extension <code>.json</code> for JSON response and <code>.xml</code> for XML.-->
</p>

<hr />

<h3 id="api-examples">Example</h3>

<p>
	Using <?= HTML::anchor('http://jquery.com', 'jQuery', array('class' => 'label label-info')) ?> to load JSONP data about event #1501:
</p>
<pre>
$.ajax({
	url:      'http://api.klubitus.org/v1/events/event',
	dataType: 'jsonp',
	data: {
		id: 1501
	},
	success: function(data, status, xhr) {
		console.log(data);
	}
});
</pre>
<p>
	JSON result in console:
</p>
<pre>
	{
		"version": "v1",
		"events":  [
			{
				"id":"1501",
				"name":"massive - the birthday bash",
				"homepage":"",
				"stamp_begin":"1023555600",
				"stamp_end":"1023602400",
				"venue":"kutsulla",
				"city":"järvenpää",
				"country":"",
				"dj":"bella & marski, damien & yamo, sakura & bobby s & kaiu, erno, hopee, nigel, quu, phaze, jay logic",
				"info":" ",
				"age":"0",
				"price":"-1.00",
				"created":"1014461180",
				"modified":"1023514641",
				"flyer_front":null,
				"flyer_front_thumb":null,
				"flyer_front_icon":null,
				"favorite_count":"0",
				"music":"kiksu, pesukonetechno, progepsyketechnohouse, polkka, chill..",
				"url":"http://alpha.klubitus.org/event/1501-massive-the-birthday-bash"
			}
		]
	}
</pre>

<hr />

<h3 id="api-events">Events</h3>

<h4 id="api-events-common">Common parameters</h4>

<?= self::parameter_table(array(
	'field' => array(self::code_array(Controller_Events_API::$_fields), 'Fetchable fields.')
)) ?>

<hr />

<h4 id="api-events-browse">Browse</h4>

<pre>http://api.klubitus.org/v1/events/browse?<em>{parameters}</em></pre>

<?= self::parameter_table(array(
	'field' => array('', HTML::anchor('#api-events-common', 'See common parameters.')),
	'from'  => array('
<code>today</code> (default)<br />
<em>unix timestamp</em><br />
<em>textual datetime description</em> parsed with <strong>strtotime</strong>, e.g. <code>yesterday</code>, <code>next monday</code>, <code>last month</code>
', 'Initial date to start browsing.'),
	'limit' => array('
<em>count</em>, max 500<br />
<em>date span</em>, e.g. <code>1m</code>, <code>1w</code> (default), <code>1d</code>
', 'How many events to load.'),
	'order' => array('
<code>asc</code> (default)<br />
<code>desc</code>
', 'Browsing order, <code>asc</code> for upcoming events, <code>desc</code> for past events.'),
)) ?>

<hr />

<h4 id="api-events-event">Event</h4>

<pre>http://api.klubitus.org/v1/events/event?<em>{parameter}</em></pre>

<?= self::parameter_table(array(
	'id' => array('<em>numeric id</em>', 'Load all data from given event, i.e. does <em>not</em> use <code>field</code> parameter.')
)) ?>

<hr />

<h4 id="api-events-search">Search</h4>

<pre>http://api.klubitus.org/v1/events/search?<em>{parameters}</em></pre>

<?= self::parameter_table(array(
	'field'  => array('', HTML::anchor('#api-events-common', 'See common parameters.')),
	'filter' => array('
<code>upcoming</code><br />
<code>past</code><br />
<code>date:<em>from-to</em></code><br />
', 'Filter results by date. <code>from</code> and <code>to</code> are unix timestamps and you may leave either one empty to query for events up to or onwards.'),
	'limit'  => array('<em>count</em>, max 500<br />', 'How many events to search.'),
	'order'  => array('<em>field.order</em>, e.g. <code>name.asc</code>, <code>city.asc:name.asc</code>', 'Sort search results by this field, supports multiple fields with colon as separator'),
	'q'      => array('<em>search term</em>, minimum 3 characters', 'Term to search for.'),
	'search' => array(self::code_array(Controller_Events_API::$_searchable), 'Field(s) to search from.'),
)) ?>

<hr />

<hr />

<h3 id="api-users">Users</h3>

<h4 id="api-users-search">Search</h4>

<pre>http://api.klubitus.org/v1/users/search?<em>{parameters}</em></pre>

<?= self::parameter_table(array(
	'field'  => array(self::code_array(Controller_Users_API::$_fields), 'Fetchable fields.'),
	'limit'  => array('<em>count</em>, max 500<br />', 'How many users to search.'),
	'order'  => array('<em>field.order</em>, e.g. <code>gender.desc</code>, <code>gender.desc:username.asc</code>', 'Sort search results by this field, supports multiple fields with colon as separator'),
	'q'      => array('<em>search term</em>, minimum 2 characters', 'Term to search for.'),
	'search' => array(self::code_array(Controller_Users_API::$_searchable), 'Field(s) to search from.'),
	'user'   => array('<em>user id</em>', 'Prioritize friends if given.')
)) ?>

<h3 id="api-venues">Venues</h3>

<h4 id="api-venues-search">Search</h4>

<pre>http://api.klubitus.org/v1/venues/search?<em>{parameters}</em></pre>

<?= self::parameter_table(array(
	'field'  => array(self::code_array(Controller_Venues_API::$_fields), 'Fetchable fields.'),
	'limit'  => array('<em>count</em>, max 500<br />', 'How many venues to search.'),
	'order'  => array('<em>field.order</em>, e.g. <code>name.asc</code>, <code>city.asc:name.asc</code>', 'Sort search results by this field, supports multiple fields with colon as separator'),
	'q'      => array('<em>search term</em>, minimum 3 characters', 'Term to search for.'),
	'search' => array(self::code_array(Controller_Events_API::$_searchable), 'Field(s) to search from.'),
)) ?>

<?php

		return ob_get_clean();
	}

}
