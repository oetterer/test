<?php
/**
 * @license GNU GPL v3+
 * @since 1.0
 *
 * @author Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents;

/**
 * This file is loaded, when using composer. Defers initialization to wfLoadExtension as if called by LocalSettings.
 *
 * @see https://github.com/oetterer/BootstrapComponents/
 */

if ( defined( 'BOOTSTRAP_COMPONENTS_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

BootstrapComponents::load();

/**
 * @codeCoverageIgnore
 */
class BootstrapComponents {

	public static function load() {

		self::doCheckRequirements();
		define( 'BOOTSTRAP_COMPONENTS_VERSION', 0.6 );
		wfLoadExtension( 'BootstrapComponents' );
	}

	public static function doCheckRequirements() {

		if ( !defined( 'MEDIAWIKI' ) ) {
			die( 'This file is part of a Mediawiki Extension, it is not a valid entry point.' . PHP_EOL );
		}

		if ( version_compare( $GLOBALS[ 'wgVersion' ], '1.27', 'lt' ) ) {
			die(
				'<b>Error:</b> <a href="https://github.com/oetterer/BootstrapComponents/">Bootstrap Components</a> '
				. 'is only compatible with MediaWiki 1.27 or above. You need to upgrade MediaWiki first.' . PHP_EOL
			);
		}
	}

	/**
	 * Returns version number of Extension BootstrapComponents
	 * @return float
	 */
	public static function getVersion() {
		return BOOTSTRAP_COMPONENTS_VERSION;
	}
}
