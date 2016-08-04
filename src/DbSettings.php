<?php

namespace OsmLint;

class DbSettings {
    private $pgHost, $pgUser, $pgPassword, $pgDatabase;

    public function getPostgresHost() {
        $this->loadPg();
        return $this->pgHost;
    }

    public function getPostgresUser() {
        $this->loadPg();
        return $this->pgUser;
    }

    public function getPostgresPassword() {
        $this->loadPg();
        return $this->pgPassword;
    }

    public function getPostgresDatabase() {
        $this->loadPg();
        return $this->pgDatabase;
    }

    private function loadPg() {
        if ( $this->pgUser && $this->pgHost ) {
            return;
        }
        $file = getenv( 'HOME' ) . '/.pg_credentials';
        $data = parse_ini_file( $file, true );

        $osmlint = $data['osmlint'];
        $this->pgHost = $osmlint['host'];
        $this->pgUser = $osmlint['user'];
        $this->pgPassword = $osmlint['password'];
        $this->pgDatabase = $osmlint['database'];
    }
}
