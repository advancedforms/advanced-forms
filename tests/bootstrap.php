<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Advanced_Forms
 */

use AdvancedFormsTests\Utils\Debug;

if ( PHP_MAJOR_VERSION >= 8 ) {
	echo "The scaffolded tests cannot currently be run on PHP 8.0+. See https://github.com/wp-cli/scaffold-command/issues/285" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Load composer and packages
require_once './vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createUnsafeMutable( '.' );
$dotenv->load();

// Set up debug logging for use during tests. The defined file must exist or errors will be thrown during tests.
require_once 'Utils/Debug.php';
if ( $log_file = getenv( 'TEST_LOG_FILE' ) and $log_file_path = realpath( $log_file ) ) {
	Debug::$logfile = $log_file_path;
}

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	$wp_content_dir = dirname( dirname( __DIR__ ) );
	$af_dir = dirname( dirname( __FILE__ ) );
	require $wp_content_dir . '/advanced-custom-fields-pro/acf.php';
	require $af_dir . '/advanced-forms.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require "{$_tests_dir}/includes/bootstrap.php";
