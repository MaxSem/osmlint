<?php

namespace OsmLint;

abstract class QueryAccumulator {
    /** @var string */
    protected $dbName;

    /** @var int */
    protected $batchSize = 1000;

    /** @var Environment */
    protected $environment;

    protected $data = [];

	/** @var ResultSet */
	protected $resultSet;

	public function __construct( $dbName, Environment $environment, ResultSet $resultSet ) {
        $this->dbName = $dbName;
        $this->environment = $environment;
        $this->resultSet = $resultSet;
    }

    public function add( $data, $key = null ) {
		if ( $key === null ) {
			$this->data[] = $data;
		} else {
			$this->data[$key] = $data;
		}

        if ( count( $this->data ) >= $this->batchSize ) {
            $this->flush();
		}
    }

    protected function connect() {
        return Mysql::connect( $this->dbName, $this->environment );
    }

    public function flush() {
		if ( $this->data ) {
			$this->flushInternal();
			$this->data = [];
		}
	}

	protected function titleDbForm( $title ) {
		return str_replace( ' ', '_', $title );
	}

    protected abstract function flushInternal();
}
