<?php

namespace OsmLint;

abstract class QueryAccumulator {
    /** @var string */
    protected $dbName;

    /** @var int */
    protected $batchSize;

    /** @var Environment */
    protected $environment;

    protected $data = [];

    public $results = [];

    public function __construct( $dbName, $environment, $batchSize = 1000 ) {
        $this->dbName = $dbName;
        $this->environment = $environment;
        $this->batchSize = $batchSize;
    }

    public function add( $data ) {
        $this->data[] = $data;

        if ( count( $this->data ) >= $this->batchSize ) {
            $this->flush();
        }
    }

    protected function connect() {
        return Mysql::connect( $this->dbName, $this->environment );
    }

    public abstract function flush();
}
