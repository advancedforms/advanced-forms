<?php

try {
	WP_CLI::add_command( AdvancedFormsDevCli::COMMAND, AdvancedFormsDevCli::class );
} catch ( \Exception $e ) {

}

class AdvancedFormsDevCli extends \WP_CLI_Command {

	const COMMAND = 'afdev';
	const PLUGIN_DIR_NAME = 'advanced-forms';
	const PLUGIN_PRO_DIR_NAME = 'advanced-forms-pro';
	const EXCLUSIONS = [
		'.*',
		'tests',
		'bin',
		'docs',
		'readme',
		'README.md',
		'phpunit.xml.dist',
		'node_modules',
		'package.json',
		'package-lock.json',
		'webpack.mix.js',
		//'src/asset/src',
		'vendor',
		'composer.lock',
		'composer.json',
		'gulpfile.js',
		'assets/images/*.psd',
	];
// todo - check dir names VS zip file names

	/**
	 * @subcommand update-lang
	 */
	public function update_lang() {
		$exclusions = implode( ' ', array_map( function ( $exc ) {
			return sprintf( '--exclude="%s"', $exc );
		}, self::EXCLUSIONS ) );

		// Only run this on the pro branch to ensure we get all the strings.
		if ( ! AF()->pro ) {
			WP_CLI::error( 'You need to be on the pro branch to run this command as that will generate translations for the whole plugin. Once generated there, we can commit here in the free branch and then merge it back into pro.' );
		}

		// Rebuild the .pot file
		$year = date( 'Y' );
		WP_CLI::runcommand( "i18n make-pot . language/advanced-forms.pot $exclusions --package-name='Advanced Forms' --subtract=.ignore.pot --file-comment='Copyright (C) $year Hookturn'" );

		// Update any existing .po files
		WP_CLI::runcommand( "i18n update-po language/advanced-forms.pot language" );

		// Rebuild the .mo files
		WP_CLI::runcommand( "i18n make-mo language" );
	}

	/**
	 * Creates a release archive with the version name appended to the archive file. e.g; acf-custom-database-tables-v1.2.3.zip
	 *
	 * ## OPTIONS
	 *
	 * [--suffix=<suffix>]
	 * : Optional suffix to append to end of archive file name. Leading hyphen is automatically added.
	 *
	 * @subcommand make-free-release-zip
	 */
	public function make_free_release( $args, $assoc_args ) {
		self::make_release_zip( WP_CONTENT_DIR . '/releases/free', self::PLUGIN_DIR_NAME, $args, $assoc_args );
	}

	/**
	 * Creates and loads a directory in our SVN files ready to deploy to WordPress.org.
	 *
	 * @subcommand make-free-release-svn
	 */
	public function make_free_release_svn( $args, $assoc_args ) {
		$svn_dir = WP_CONTENT_DIR . '/releases/free-svn';
		$plugin_dir = WP_CONTENT_DIR . '/plugins/' . self::PLUGIN_DIR_NAME;
		$svn_trunk_dir = $svn_dir . '/trunk';

		// Update SVN files
		exec( 'cd ' . $svn_dir . '&& svn up', $output );

		// Clear trunk dir
		exec( 'rm -rf ' . $svn_trunk_dir, $output );

		// Copy release files to trunk
		// Exclude all these from the release
		$exclusions = implode( ' ', array_map( function ( $exc ) {
			return sprintf( '--exclude="%s"', $exc );
		}, self::EXCLUSIONS ) );
		$shell_command = /** @lang Bash */
			"
            rsync -a $exclusions $plugin_dir/ $svn_trunk_dir \
            && open $svn_dir;
			";
		exec( $shell_command, $output );

		// Get the current tag
		$version = get_plugin_data( "$plugin_dir/" . self::PLUGIN_DIR_NAME . ".php", false, false )['Version'];

		// Remove current tagged release dir, if there is one. We're going to replace it.
		$tagged_dir = $svn_dir . '/tags/' . $version;
		exec( 'rm -rf ' . $tagged_dir, $output );

		// Copy trunk to new tagged release dir
		exec( "cd $svn_dir && svn cp {$svn_trunk_dir}/. $tagged_dir", $output );

		// Check in the new code using svn ci -m "tagging version $version"
		//exec( 'cd ' . $svn_dir . '&& svn ci -m "tagging version "' . $version, $output );

		WP_CLI::success( print_r( $output ) );
	}

	/**
	 * Creates a release archive with the version name appended to the archive file. e.g; acf-custom-database-tables-v1.2.3.zip
	 *
	 * ## OPTIONS
	 *
	 * [--suffix=<suffix>]
	 * : Optional suffix to append to end of archive file name. Leading hyphen is automatically added.
	 *
	 * @subcommand make-pro-release-zip
	 */
	public function make_pro_release( $args, $assoc_args ) {
		self::make_release_zip( WP_CONTENT_DIR . '/releases/pro', self::PLUGIN_PRO_DIR_NAME, $args, $assoc_args );
	}

	/**
	 * Makes the release ZIP file.
	 *
	 * @param $releases_dir
	 * @param string $release_dirname
	 * @param array $args
	 * @param array $assoc_args
	 */
	private function make_release_zip( $releases_dir, $release_dirname = self::PLUGIN_DIR_NAME, $args = [], $assoc_args = [] ) {
		// If shorthand syntax found, throw error
		WP_CLI::runcommand( self::COMMAND . ' check-echo-syntax' );

		// If assets are not in a prod-ready state, throw error
		//WP_CLI::runcommand( self::COMMAND . ' check-asset-state' ); // todo - perhaps run a build instead?

		// Guards passed. Start the release...

		$suffix = ( $suffix = WP_CLI\Utils\get_flag_value( $assoc_args, 'suffix' ) )
			? '-' . $suffix
			: '';

		// Always develop using `advanced-forms` dir but we release pro version under `advanced-forms-pro`
		$dirname = self::PLUGIN_DIR_NAME;
		$plugin_dev_dir = WP_CONTENT_DIR . '/plugins/' . $dirname;

		$data = get_plugin_data( "$plugin_dev_dir/$dirname.php", false, false );
		$version = ( isset( $data['Version'] ) and $data['Version'] )
			? '-v' . $data['Version']
			: '';

		// Exclude all these from the release
		$exclusions = implode( ' ', array_map( function ( $exc ) {
			return sprintf( '--exclude="%s"', $exc );
		}, self::EXCLUSIONS ) );

		$shell_command = /** @lang Bash */
			"
            rsync -a $exclusions $plugin_dev_dir/ $releases_dir/$release_dirname \
            && cd $releases_dir \
            && zip -rm {$release_dirname}{$version}{$suffix}.zip $release_dirname \
            && cd - \
            && open $releases_dir;
			";

		exec( $shell_command, $output );

		foreach ( $output as $line ) {
			WP_CLI::log( $line );
		}

		\WP_CLI::success( 'Done.' );
	}

	/**
	 * Searches all files for shorthand echo syntax and reports on those.
	 *
	 * @param $args
	 * @param $assoc_args
	 *
	 * @subcommand check-echo-syntax
	 */
	public function check_echo_syntax( $args, $assoc_args ) {
		$dir = WP_CONTENT_DIR . '/plugins/' . self::PLUGIN_DIR_NAME;

		exec( 'grep --recursive --line-number --exclude-dir={bin,tests,vendor,language,node_modules} --include=*.php "<?=" ' . $dir, $hits );

		if ( $hits ) {
			$output = 'Found shorthand echo syntax in the following locations:';

			foreach ( $hits as $hit ) {
				// Remove the base path
				$hit = str_replace( $dir, '', $hit );

				// Remove the actual match after the filenumber
				$parts = explode( ':', $hit );
				array_pop( $parts );
				$hit = implode( ':', $parts );

				$output .= PHP_EOL . "\t — " . $hit;
			}

			WP_CLI::error( $output );
		}
	}

//	/**
//	 * Checks to see if assets are in a state ready for production. i.e; built/minified.
//	 *
//	 * @subcommand check-asset-state
//	 */
//	public function check_asset_state() {
//		$dir = WP_CONTENT_DIR . '/plugins/' . self::PLUGIN_DIR_NAME;
//
//		exec( "find $dir/src/asset/build/js -name '*.js.map'", $hits );
//
//		if ( ! $hits ) {
//			WP_CLI::error( 'No JS source maps were found in src/asset/build/js. You need to run `npm run prod` before creating a release.' );
//		} else {
//			WP_CLI::success( 'Assets appear to be in a prod ready state.' );
//		}
//	}

}
