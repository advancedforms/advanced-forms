<?php

namespace {

	use AdvancedFormsTests\Utils\Debug;

	if ( ! function_exists( 'dlog' ) ) {
		function dlog( $data, $export = true, $informative = false, $mode = 'a' ) {
			Debug::log( $data, $informative, $mode, ! $export );
		}
	}

	if ( ! function_exists( 'd' ) ) {
		function d( $data ) {
			ini_set( 'xdebug.var_display_max_depth', '10' );
			ini_set( 'xdebug.var_display_max_children', '256' );
			ini_set( 'xdebug.var_display_max_data', '1024' );
			Debug::d( $data );
		}
	}

	if ( ! function_exists( 'dd' ) ) {
		function dd( $data ) {
			ini_set( 'xdebug.var_display_max_depth', '10' );
			ini_set( 'xdebug.var_display_max_children', '256' );
			ini_set( 'xdebug.var_display_max_data', '1024' );
			Debug::dd( $data );
		}
	}

	if ( ! function_exists( 'qlog_start' ) ) {
		function qlog_start() {
			Debug::start_logging_queries();
		}
	}

	if ( ! function_exists( 'qlog_end' ) ) {
		function qlog_end() {
			Debug::stop_logging_queries();
		}
	}
}

namespace AdvancedFormsTests\Utils {

	use DateTime;
	use DateTimeZone;

	class Debug {

		public static $logfile;

		static function dd( $data ) {
			echo '<pre>';
			var_dump( $data );
			echo '</pre>';
			die();
		}

		static function d( $data ) {
			echo '<pre>';
			var_dump( $data );
			echo '</pre>';
		}

		/**
		 * Logs data to pds.log file
		 *
		 * @param mixed $data
		 * @param string $mode specifies write mode ('a' for append || 'w' for write)
		 * @param boolean $pretty if true, var_export is used
		 * @param boolean $informative if true, informative header is printed
		 *
		 * @throws \Exception
		 */
		static function log( $data, $informative = false, $mode = 'w', $pretty = true ) {

			if ( ! self::$logfile ) {
				throw new \Exception( 'PDK\Debug::$logfile is not set' );
			}

			$file_location = self::$logfile;

			$datetime = new DateTime; // current time = server time
			$otherTZ = new DateTimeZone( 'Australia/Melbourne' );
			$datetime->setTimezone( $otherTZ ); // calculates with new TZ now

			$bt = debug_backtrace();
			$file = "Calling file: " . basename( $bt[0]['file'] );
			$line = "Line: " . $bt[0]['line'];

			$info = '';

			if ( $informative ) {
				$info = ">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n";
				$info .= $datetime->format( 'm/d/Y h:i:s a' ) . "\n";
				$info .= $file . "\n";
				$info .= $line . "\n";
				$info .= ">>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>\n";
			}

			// Better boolean logging
			if ( $data === false ) {
				$data = '(bool)FALSE';
			} elseif ( $data === true ) {
				$data = '(bool)TRUE';
			}

			if ( $pretty ) {
				$info .= print_r( $data, true );
			} else {
				$info .= var_export( $data, true );
			}

			$info .= $informative ? "\n\n" : "\n";

			$file = fopen( $file_location, $mode ) or print( '<div style="background-color:#db514d;color:white;text-align:center;padding:10px;">Cannot open dev.log file for logging</div>' );
			fwrite( $file, $info );
			fclose( $file );
		}

		static $query_count = 0;

		static function query_logger( $query ) {
			self::$query_count ++;
			self::log( '[' . self::$query_count . ']: ' . $query, false, 'a' );

			return $query;
		}

		static function start_logging_queries() {
			static $query_count = 0;
			add_filter( 'query', [ __CLASS__, 'query_logger' ] );
		}

		static function stop_logging_queries() {
			self::log( "[Queries Logged]: " . self::$query_count );
			self::$query_count = 0;
			remove_filter( 'query', [ __CLASS__, 'query_logger' ] );
		}

		public static function should_break( $condition = null ) {
			if ( is_null( $condition ) ) {
				return $GLOBALS['cdt-debug-breakpoints'] ?? false;
			}

			$GLOBALS['cdt-debug-breakpoints'] = (bool) $condition;
		}

	}

}