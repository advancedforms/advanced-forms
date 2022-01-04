<?php

class AF_Core_Migrations {
	const VERSION_KEY = 'af_version';
	const LOCK_EXPIRY_KEY = 'af_migration_lock_expiry';
	const LOCK_DURATION_SECONDS = 5 * 60; // 5 minutes

	function __construct() {
		add_action( 'init', array( $this, 'check_migration' ) );
	}

	function migrations() {
		$migrations = array();
		$migrations = apply_filters( 'af/migrations', $migrations );
		return $migrations;
	}

	function check_migration() {
		// Check if a new version has been installed
		$new_version = AF()->version;
		$current_version = $this->get_last_version();
		if ( version_compare( $new_version, $current_version, '<=' ) ) {
			// Same version as before, no migration necessary
			return;
		}

		// To avoid having multiple processes run migrations at the same time, we use a simple lock.
		// This lock is simply a flag in the options table with an expiry. If this migration was to fail,
		// the next request after the lock has expired (5 minutes) will run the migrations again.
		if ( ! $this->acquire_lock() ) {
			return;
		}

		// Find all the migrations we need to apply
		$applicable_migrations = array_filter(
			$this->migrations(),
			function( $migration ) use ($new_version, $current_version) {
				$is_after_current = version_compare( $migration['version'], $current_version, '>' );
				$is_before_new = version_compare( $migration['version'], $new_version, '<=' );
				return $is_after_current && $is_before_new;
			}
		);

		// Sort the migrations by version, from lowest to highest
		usort( $applicable_migrations, function( $m1, $m2 ) {
			return version_compare( $m1['version'], $m2['version'] );
		});

		foreach ( $applicable_migrations as $migration ) {
			$migration['apply']();
		}

		$this->release_lock();
		$this->update_version();
	}

	function get_last_version() {
		$version = get_option( self::VERSION_KEY );

		// If the version has not yet been set then we need to set it to the
		// first version before migration support was introduced, 1.8.2.
		if ( $version === false ) {
			$version = '1.8.2';
			update_option( self::VERSION_KEY, $version );
		}

		return $version;
	}

	function update_version() {
		update_option( self::VERSION_KEY, AF()->version );
		return AF()->version;
	}

	function acquire_lock() {
		$current_lock = get_option( self::LOCK_EXPIRY_KEY );
		if ( $current_lock && time() < $current_lock ) {
			// Some other process is already performing a migration
			return false;
		}

		update_option( self::LOCK_EXPIRY_KEY, time() + self::LOCK_DURATION_SECONDS );
		return true;
	}

	function release_lock() {
		delete_option( self::LOCK_EXPIRY_KEY );
	}
}

new AF_Core_Migrations();