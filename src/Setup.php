<?php
/**
 * Contains the class doing all the necessary hook registration and helper object initialization.
 *
 * @copyright (C) 2018, Tobias Oetterer, University of Paderborn
 * @license       https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 (or later)
 *
 * This file is part of the MediaWiki extension BootstrapComponents.
 * The BootstrapComponents extension is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The BootstrapComponents extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @file
 * @ingroup       BootstrapComponents
 * @author        Tobias Oetterer
 */

namespace BootstrapComponents;

use \Bootstrap\BootstrapManager;
use \Hooks;
use \MediaWiki\MediaWikiServices;
use \Parser;
use \ReflectionClass;

/**
 * Class Setup
 *
 * Registers all hooks and components for Extension BootstrapComponents
 *
 * @since 1.0
 */
class Setup {
	/**
	 * Callback function when extension is loaded via extension.json or composer.
	 *
	 * Note: With this we omit hook registration in extension.json and define our own here
	 * to increase compatibility with composer loading
	 *
	 * @param array $info
	 *
	 * @throws \ConfigException cascading {@see \ConfigFactory::makeConfig} and {@see \BootstrapComponents\Setup::registerHooks}
	 * @throws \MWException cascading {@see \BootstrapComponents\Setup::registerHooks}
	 *
	 * @return bool
	 */
	public static function onExtensionLoad( $info ) {
		$setup = new self();
		if ( !empty( $info ) ) {
			$setup->prepareEnvironment( $info );
		}
		$setup->bootstrapExtensionPresent();
		$configFactory = MediaWikiServices::getInstance()->getConfigFactory();
		$setup->registerMyConfiguration( $configFactory );

		$setup->registerHooks(
			$configFactory->makeConfig( 'BootstrapComponents' )
		);
		return true;
	}

	/**
	 * @return \Closure
	 */
	public function createGalleryGetModes() {
		return function( &$modeArray ) {
			$modeArray['carousel'] = 'BootstrapComponents\\CarouselGallery';
			return true;
		};
	}

	/**
	 * @param NestingController $nestingController
	 * @param \Config           $myConfig
	 *
	 * @return \Closure
	 */
	public function createImageBeforeProduceHTML( $nestingController, $myConfig ) {
		return function( &$dummy, &$title, &$file, &$frameParams, &$handlerParams, &$time, &$res
		) use ( $nestingController, $myConfig ) {

			$imageModal = new ImageModal( $dummy, $title, $file, $nestingController );

			if ( $myConfig->has( 'BootstrapComponentsDisableSourceLinkOnImageModal' )
				&& $myConfig->get( 'BootstrapComponentsDisableSourceLinkOnImageModal' )
			) {
				$imageModal->disableSourceLink();
			}

			return $imageModal->parse( $frameParams, $handlerParams, $time, $res );
		};
	}

	/**
	 * @param ComponentLibrary  $componentLibrary
	 * @param NestingController $nestingController
	 *
	 * @return \Closure
	 */
	public function createParserFirstCallInitCallback( $componentLibrary, $nestingController ) {

		return function( Parser $parser ) use ( $componentLibrary, $nestingController ) {

			$parserOutputHelper = ApplicationFactory::getInstance()->getParserOutputHelper( $parser );

			foreach ( $componentLibrary->getRegisteredComponents() as $componentName ) {

				$parserHookString = $componentLibrary::compileParserHookStringFor( $componentName );
				$callback = $this->createParserHookCallbackFor(
					$componentName, $componentLibrary, $nestingController, $parserOutputHelper
				);

				if ( $componentLibrary->isParserFunction( $componentName ) ) {
					$parser->setFunctionHook( $parserHookString, $callback );
				} elseif ( $componentLibrary->isTagExtension( $componentName ) ) {
					$parser->setHook( $parserHookString, $callback );
				} else {
					wfDebugLog(
						'BootstrapComponents', 'Unknown handler type ('
							. $componentLibrary->getHandlerTypeFor( $componentName ) . ') detected for component ' . $parserHookString
					);
				}
			}
		};
	}

	/**
	 * @param string             $componentName
	 * @param ComponentLibrary   $componentLibrary
	 * @param NestingController  $nestingController
	 * @param ParserOutputHelper $parserOutputHelper
	 *
	 * @return \Closure
	 */
	public function createParserHookCallbackFor( $componentName, $componentLibrary, $nestingController, $parserOutputHelper ) {

		return function() use ( $componentName, $componentLibrary, $nestingController, $parserOutputHelper ) {

			$componentClass = $componentLibrary->getClassFor( $componentName );
			$objectReflection = new ReflectionClass( $componentClass );
			$object = $objectReflection->newInstanceArgs( [ $componentLibrary, $parserOutputHelper, $nestingController ] );

			$parserRequest = ApplicationFactory::getInstance()->getNewParserRequest(
				func_get_args(),
				$componentLibrary->isParserFunction( $componentName ),
				$componentName
			);
			/** @var AbstractComponent $object */
			return $object->parseComponent( $parserRequest );
		};
	}

	/**
	 * @return \Closure
	 */
	public function createSetupAfterCache() {
		return function() {
			BootstrapManager::getInstance()->addAllBootstrapModules();
			return true;
		};
	}

	/**
	 * Defines all hooks and the corresponding callbacks
	 *
	 * @param  \Config $myConfig
	 *
	 * @throws \ConfigException cascading {@see \BootstrapComponents\Setup::initializeApplications}
	 * @throws \MWException cascading {@see \BootstrapComponents\Setup::initializeApplications}
	 *
	 * @return \Closure[]
	 */
	public function getHooksToRegister( $myConfig ) {

		list( $componentLibrary, $nestingController ) = $this->initializeApplications( $myConfig );
		$hooks = [
			'ParserFirstCallInit' => $this->createParserFirstCallInitCallback( $componentLibrary, $nestingController ),
			'SetupAfterCache'     => $this->createSetupAfterCache(),
		];

		if ( $myConfig->has( 'BootstrapComponentsEnableCarouselGalleryMode' )
			&& $myConfig->get( 'BootstrapComponentsEnableCarouselGalleryMode' )
		) {
			$hooks['GalleryGetModes'] = $this->createGalleryGetModes();
		}
		if ( $myConfig->has( 'BootstrapComponentsModalReplaceImageThumbnail' )
			&& $myConfig->get( 'BootstrapComponentsModalReplaceImageThumbnail' )
		) {
			$hooks['ImageBeforeProduceHTML'] = $this->createImageBeforeProduceHTML( $nestingController, $myConfig );
		}

		return $hooks;
	}

	/**
	 * @param \Config $myConfig
	 *
	 * @return array
	 * @throws \MWException cascading {@see \BootstrapComponents\applicationFactory} calls
	 * @throws \ConfigException cascading {@see \Config::get}
	 */
	public function initializeApplications( $myConfig ) {
		$applicationFactory = ApplicationFactory::getInstance();
		$componentLibrary = $applicationFactory->getComponentLibrary();
		$nestingController = $applicationFactory->getNestingController(
			$myConfig->get( 'BootstrapComponentsDisableIdsForTestsEnvironment' )
		);
		return [ $componentLibrary, $nestingController ];
	}

	/**
	 * @param string $hook
	 *
	 * @return boolean
	 */
	public function isRegistered( $hook ) {
		return Hooks::isRegistered( $hook );
	}

	/**
	 * Does the actual registration of hooks and components
	 *
	 * @param  \Config $myConfig
	 *
	 * @throws \ConfigException cascading {@see \BootstrapComponents\Setup::getHooksToRegister}
	 * @throws \MWException cascading {@see \BootstrapComponents\Setup::getHooksToRegister}
	 */
	public function registerHooks( $myConfig ) {
		foreach ( $this->getHooksToRegister( $myConfig ) as $hook => $callback ) {
			Hooks::register( $hook, $callback );
		}
	}

	/**
	 * @param \ConfigFactory $configFactory
	 * Registers my own configuration, so that it is present during onLoad. See phabricator issue T184837
	 *
	 * @link https://phabricator.wikimedia.org/T184837
	 */
	public function registerMyConfiguration( $configFactory ) {
		$configFactory->register( 'BootstrapComponents', 'GlobalVarConfig::newInstance' );
	}

	private function bootstrapExtensionPresent() {
		if ( !defined( 'BS_VERSION' ) ) {
			die(
				'This extension requires Extension Bootstrap to be installed. '
				. 'Please check <a href="https://github.com/oetterer/BootstrapComponents/">the online help</a>' . PHP_EOL
			);
		}
	}

	/**
	 * Information array created on extension registration.
	 * Note: this array also resides as from ExtensionRegistry::getInstance()->getAllThings()['BootstrapComponents']
	 *
	 * @param array $info
	 */
	private function prepareEnvironment( $info ) {
		define( 'BOOTSTRAP_COMPONENTS_VERSION', (string) $info['version'] );
	}
	### attend before deployment
	# mandatory
	#@todo put on github with automatic testing and scrutinizing
	#@todo create composer package. see https://packagist.org/ and https://packagist.org/about#how-to-update-packages; packet name "bootstrap-components"
	#@todo recheck code for https://www.mediawiki.org/wiki/Security_checklist_for_developers#Dynamic_code_generation > Any user input: no isset!
	#@todo complete documentation in /doc (installation, configuration, howto, expansion)
	#@todo change release date in docs/release-notes.md


	# code improvement
	#@todo add more comments
	#@todo introduce integration test; require-dev smw seems the easiest way to do this. decide, if working with 3.0.0 or 2.5.(4|5)
	# or use parser tests instead. see https://www.mediawiki.org/wiki/Parser_tests
	# 1. adjust composer.json, integration script, create tests/parser/parserTests.txt 2. copy all image related stuff from mw/tests/parser/parserTests.txt
	# still thinking about integration tests, using smw. increases code coverage report data
	/*
	 * 	"parser":[
			"echo '$wgBootstrapComponentsDisableIdsForTestsEnvironment = true;' >> ../../LocalSettings.php",
			"php ../../tests/parserTests.php --quiet --file tests/parser/parserTests.txt --quiet"
		],
	in composer does not work, because mw >= 1.29 has parserTests.php moved to tests/parser/parserTests.php; need test script...
	 */
	#@fixme tests/parser/parserTests.txt (after previous todo)
	#@todo you can increase code coverage by testing private and protected methods directly
	# see https://jtreminio.com/2013/03/unit-testing-tutorial-part-3-testing-protected-private-methods-coverage-reports-and-crap/
	# when starting to use this, revert some previously exposed methods to protected/private again.


	### this remains
	#@todo when dropping support for mw > 1.31, replace manual class autoloading in extension.json with psr-4 autoloading
	#@todo add extensions requirement to extension.json for "Bootstrap": "~ 1.2" as soon as Bootstrap supports new Extension loading (leaving this in breaks 1.31.x)
	#@todo remove \BootstrapComponents\Setup::registerMyConfiguration when dropping support for mw > 1.31 (assuming T184837 will be fixed)
}