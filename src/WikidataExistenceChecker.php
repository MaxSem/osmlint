<?php

namespace OsmLint;

class WikidataExistenceChecker extends PageExistenceChecker  {
	protected $errors = [
		'redirect' => 'Wikidata item is a redirect',
		'nonexistent' => "Wikidata item doesn't exist",
	];

	public function __construct( Environment $environment, ResultSet $resultSet ) {
		parent::__construct( 'wikidatawiki', $environment, $resultSet );
	}
}
