<?php

namespace OsmLint;


class ExistenceHolder {
	/** @var PageExistenceChecker[] */
	private $wikis = [];
	/** @var Environment */
	private $environment;
	/** @var ResultSet */
	private $resultSet;

	public function __construct( Environment $environment, ResultSet $resultSet ) {
		$this->environment = $environment;
		$this->resultSet = $resultSet;
	}

	public function addTitle( $wiki, $title, $object ) {
		if ( !isset( $this->wikis[$wiki] ) ) {
			$this->wikis[$wiki] = new PageExistenceChecker( $wiki, $this->environment, $this->resultSet );
		}
		$this->wikis[$wiki]->add( $object, $title );
	}

	public function flush() {
		foreach ( $this->wikis as $wiki ) {
			$wiki->flush();
		}
	}
}
