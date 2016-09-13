<?php

namespace OsmLint;

use DateTime;
use DateTimeZone;

class UI {
	const MAIN_HEADING = 'Problems connecting OSM and Wikipedia/Wikidata';

	private $request;
	private $environment;
	private $checker;
	private $dataFile;

	public function __construct( array $request, Environment $environment ) {
		$this->request = $request;
		$this->environment = $environment;
		$this->checker = new Checker( $environment );
		$this->dataFile = __DIR__ . '/../../problems.json';
	}

	public function show() {
		if ( !isset( $this->request['cat'] ) ) {
			$this->showTotals();
		} else {
			if ( isset( $this->request['raw'] ) ) {
				$this->outputRawCategory( $this->request['cat'] );
			} else {
				$this->showCategory( $this->request['cat'] );
			}
		}
	}

	private function error( $code, $msg ) {
		http_response_code( $code );
		echo( htmlspecialchars( $msg ) );
		die( -1 );
	}

	private function showTotals() {
		$data = $this->getData();
		$this->showHeader( self::MAIN_HEADING );

		echo "\t<ul>\n";
		foreach ( $data as $name => $objects ) {
			$link = htmlspecialchars( '?cat=' . urlencode( $name ) );
			$name = htmlspecialchars( $name );
			$count = count( $objects );
			if ( $count == ResultSet::MAX_CATEGORY_SIZE ) {
				$count .= '+';
			}

			echo "\t\t<li><b><a href=\"$link\">$name</a></b> (<a href=\"$link&amp;raw\">raw</a>) <small>$count result(s)</small></li>\n";
		}
		echo  "\t</ul>\n";


		$this->showFooter();
	}

	private function showCategory( $name ) {
		$data = $this->getCategory( $name );

		$this->showHeader( $name );

		$this->showBreadCrumbs( self::MAIN_HEADING, $name );

		echo <<<HTML
<table>
<tr><th>OSM ID</th><th>Type</th><th>Name</th><th>Wikipedia</th><th>Wikidata</th></tr>
HTML;

		foreach ( $data as $object ) {
			$object = (object)$object;

			$type = htmlspecialchars( $object->type );
			$osmLink = $this->osmLink( $object );
			$name = htmlspecialchars( $object->name );
			$wpLink = $this->wpLink( $object );
			$wdLink = $this->wdLink( $object );

			echo "<tr><td>$osmLink</td><td>$type</td><td>$name</td><td>$wpLink</td><td>$wdLink</td></tr>";
		}

		echo "</table>\n";

		$this->showFooter();
	}

	private function osmLink( $object ) {
		switch ( $object->type ) {
			case 'point':
				$ot = 'node';
				break;
			default:
				return $object->osm_id;
		}

		return "<a href=\"https://www.openstreetmap.org/edit?{$ot}={$object->osm_id}\">{$object->osm_id}</a>";
	}

	private function wpLink( $object ) {
		if ( $this->checker->checkWikipediaLinkQuick( $object ) === null ) {
			$parts = explode( ':', $object->wikipedia, 2 );
			$escaped = htmlspecialchars( $object->wikipedia );
			$link = htmlspecialchars( "https://{$parts[0]}.wikipedia.org/wiki/" . urlencode( str_replace( ' ', '_', $parts[1] ) ) );
			return "<a href=\"$link\">$escaped</a>";
		} elseif ( $object->wikipedia === null ) {
			return '<em>none</em>';
		}

		return htmlspecialchars( $object->wikipedia );
	}

	private function wdLink( $object ) {
		if ( $this->checker->checkWikidataLinkQuick( $object ) === null ) {
			$link = htmlspecialchars( "https://www.wikidata.org/wiki/{$object->wikidata}" );
			return "<a href=\"$link\">{$object->wikidata}</a>";
		} elseif ( $object->wikidata === null ) {
			return '<em>none</em>';
		}

		return htmlspecialchars( $object->wikidata );	}

	private function showBreadCrumbs( $main, $sub ) {
		$main = htmlspecialchars( $main );
		$link = htmlspecialchars( '?cat=' . urlencode( $sub ) );
		$sub = htmlspecialchars( $sub );

		echo "\t<div id=\"breadcrumbs\"><a href=\".\">$main</a> » <a href=\"$link\">$sub</a></div>";
	}

	private function outputRawCategory( $name ) {
		$cat = $this->getCategory( $name );
		header( 'Content-Type: application/json' );
		echo json_encode( $cat, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT );
	}

	private function getData() {
		$data = @file_get_contents( $this->dataFile );
		if ( $data === false ) {
			$this->error( 500, 'Error reading data file' );
		}
		return json_decode( $data, true );
	}

	private function getCategory( $name ) {
		$data = $this->getData();
		if ( !isset( $data[$name] ) ) {
			$this->error( 404, 'Invalid category given' );
		}
		return $data[$name];
	}

	private function showHeader( $title ) {
		$title = htmlspecialchars( $title );
		echo <<<HTML
<!doctype html>
<title>$title</title>
<style>
	body { font-family: sans-serif; }
	#breadcrumbs { font-size: 92%;  margin-left: 4em; margin-bottom: 2em; }
	table, td, th { border: 1px solid black; border-collapse: collapse; }
	#bottom { margin-top: 2em; }
</style>
<body>
<h1>$title</h1>
HTML;
	}

	private function showFooter() {
		$time = stat( $this->dataFile )['mtime'];
		$diff = self::timeElapsed( "@$time" );
		echo <<<HTML
Last updated $diff.
<a href="https://github.com/MaxSem/osmlint"><img style="position: absolute; top: 0; right: 0; border: 0;" src="https://camo.githubusercontent.com/365986a132ccd6a44c23a9169022c0b5c890c387/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f7265645f6161303030302e706e67" alt="Fork me on GitHub" data-canonical-src="https://s3.amazonaws.com/github/ribbons/forkme_right_red_aa0000.png"></a>
<div id="bottom"><a href="https://tools.wmflabs.org/"><img src="https://tools-static.wmflabs.org/static/logos/powered-by-tool-labs.png" /></a></div>
</body>
HTML;
	}

	/**
	 * From http://stackoverflow.com/a/18602474/1033612
	 * @param int $datetime
	 * @param bool $full
	 * @return string
	 */
	private static function timeElapsed($datetime, $full = false) {
		$utc = new DateTimeZone( 'UTC' );
		$now = new DateTime( 'now', $utc );
		$ago = new DateTime( $datetime, $utc );
		$diff = $now->diff($ago);

		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;

		$string = array(
			'y' => 'year',
			'm' => 'month',
			'w' => 'week',
			'd' => 'day',
			'h' => 'hour',
			'i' => 'minute',
			's' => 'second',
		);
		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
			} else {
				unset($string[$k]);
			}
		}

		if (!$full) $string = array_slice($string, 0, 1);
		return $string ? implode(', ', $string) . ' ago' : 'just now';
	}
}
