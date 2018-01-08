<?php

if ( PHP_SAPI !== 'cli' ) {
	die( 'Not an entry point' );
}

error_reporting( E_ALL | E_STRICT );
date_default_timezone_set( 'UTC' );
ini_set( 'display_errors', 1 );

if ( !ExtensionRegistry::getInstance()->isLoaded( 'BootstrapComponents' ) ) {
	die( "\nBootstrapComponents is not available or loaded, please check your Composer or LocalSettings.\n" );
}

$version = print_r( ExtensionRegistry::getInstance()->getAllThings()['BootstrapComponents']['version'], true );

print sprintf( "\n%-30s%s\n", "BootstrapComponents: ", $version );

# @fixme obsolete with psr-4?
require_once ( __DIR__. '/phpunit/Unit/ComponentsTestBase.php' );