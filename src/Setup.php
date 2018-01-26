<?php
/**
 * Contains the class doing preparing the environment and registering the needed/wanted hooks.
 *
 * @copyright (C) 2018, Tobias Oetterer, Paderborn University
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
use \MWException;
use \Parser;
use \ReflectionClass;
/**
 * Class Setup
 *
 * Registers all hooks and components for Extension BootstrapComponents.
 *
 * Information on how to add an additional hook
 *  1. add it to {@see Setup::AVAILABLE_HOOKS}.
 *  2. add an appropriate entry in the array inside {@see Setup::getCompleteHookDefinitionList}
 *     with the hook as array key and the callback as value.
 *  3. have {@see Setup::compileRequestedHooksListFor} add the hook to its result array. Based on
 *     a certain condition, if necessary.
 *  4. add appropriate tests to {@see \BootstrapComponents\Tests\Unit\SetupTest}.
 *
 * @since 1.0
 */
class Setup {

	/**
	 * @var array
	 */
	const AVAILABLE_HOOKS = [ 'GalleryGetModes', 'ImageBeforeProduceHTML', 'ParserBeforeTidy', 'ParserFirstCallInit', 'SetupAfterCache' ];

	/**
	 * @var ComponentLibrary
	 */
	private $componentLibrary;

	/**
	 * @var \Config
	 */
	private $myConfig;

	/**
	 * @var NestingController
	 */
	private $nestingController;

	/**
	 * Callback function when extension is loaded via extension.json or composer.
	 *
	 * Note: With this we omit hook registration in extension.json and define our own here
	 * to better allow for unit testing.
	 *
	 * @param array $info
	 *
	 * @throws \ConfigException cascading {@see Setup::run}
	 * @throws \MWException cascading {@see Setup::__construct()} and {@see Setup::run}
	 *
	 * @return bool
	 */
	public static function onExtensionLoad( $info ) {
		$setup = new self( $info );

		$setup->run();
		return true;
	}

	/**
	 * Setup constructor.
	 *
	 * @param $info
	 *
	 * @throws \ConfigException cascading {@see \BootstrapComponents\Setup::getHooksToRegister}
	 * @throws \MWException cascading {@see \BootstrapComponents\Setup::getHooksToRegister}
	 *
	 */
	public function __construct( $info ) {

		$this->assertExtensionBootstrapPresent();

		if ( !empty( $info['version'] ) ) {
			$this->prepareEnvironment( $info['version'] );
		}

		$configFactory = MediaWikiServices::getInstance()->getConfigFactory();
		$this->registerMyConfiguration( $configFactory );
		$this->myConfig = $configFactory->makeConfig( 'BootstrapComponents' );

		list( $this->componentLibrary, $this->nestingController ) = $this->initializeApplications( $this->myConfig );
	}

	/**
	 * @param array $hooksToRegister
	 *
	 * @return array
	 */
	public function buildHookCallbackListFor( $hooksToRegister ) {
		$hookCallbackList = [];
		$completeHookDefinitionList = $this->getCompleteHookDefinitionList(
			$this->myConfig, $this->componentLibrary, $this->nestingController
		);
		foreach ( $hooksToRegister as $requestedHook ) {
			if ( isset( $completeHookDefinitionList[$requestedHook] ) ) {
				$hookCallbackList[$requestedHook] = $completeHookDefinitionList[$requestedHook];
			}
		}
		return $hookCallbackList;
	}

	/**
	 * @throws \MWException cascading {@see \Hooks::clear}
	 */
	public function clear() {
		foreach ( self::AVAILABLE_HOOKS as $name ) {
			Hooks::clear( $name );
		}
	}

	/**
	 * @param \Config $myConfig
	 *
	 * @throws \ConfigException cascading {@see \Config::get}
	 *
	 * @return string[]
	 */
	public function compileRequestedHooksListFor( $myConfig ) {
		$requestedHookList = [ 'ParserFirstCallInit', 'SetupAfterCache' ];
		if ( $myConfig->has( 'BootstrapComponentsEnableCarouselGalleryMode' )
			&& $myConfig->get( 'BootstrapComponentsEnableCarouselGalleryMode' )
		) {
			$requestedHookList[] = 'GalleryGetModes';
		}
		if ( $myConfig->has( 'BootstrapComponentsModalReplaceImageTag' )
			&& $myConfig->get( 'BootstrapComponentsModalReplaceImageTag' )
		) {
			$requestedHookList[] = 'ImageBeforeProduceHTML';
			$requestedHookList[] = 'ParserBeforeTidy';
		}
		return $requestedHookList;
	}

	/**
	 * @param \Config           $myConfig
	 * @param ComponentLibrary  $componentLibrary
	 * @param NestingController $nestingController
	 *
	 * @return \Closure[]
	 */
	public function getCompleteHookDefinitionList( $myConfig, $componentLibrary, $nestingController ) {
		return [
			'GalleryGetModes' => function( &$modeArray ) {
				$modeArray['carousel'] = 'BootstrapComponents\\CarouselGallery';
				return true;
			},
			'ImageBeforeProduceHTML' => function( &$dummy, &$title, &$file, &$frameParams, &$handlerParams, &$time, &$res
			) use ( $nestingController, $myConfig ) {

				$imageModal = new ImageModal( $dummy, $title, $file, $nestingController );

				if ( $myConfig->has( 'BootstrapComponentsDisableSourceLinkOnImageModal' )
					&& $myConfig->get( 'BootstrapComponentsDisableSourceLinkOnImageModal' )
				) {
					$imageModal->disableSourceLink();
				}

				return $imageModal->parse( $frameParams, $handlerParams, $time, $res );
			},
			'ParserBeforeTidy' => function( &$parser, &$text ) {
				// injects right before the tidy marker report (e.g. <!-- Tidy found no errors -->), at the very end of the wiki text content
				$parserOutputHelper = ApplicationFactory::getInstance()->getParserOutputHelper( $parser );
				$text .= $parserOutputHelper->getContentForLaterInjection();
				return true;
			},
			'ParserFirstCallInit' => $this->createParserFirstCallInitCallback( $componentLibrary, $nestingController ),
			'SetupAfterCache' => function() {
				BootstrapManager::getInstance()->addAllBootstrapModules();
				return true;
			},
		];
	}

	/**
	 * @param \Config $myConfig
	 *
	 * @throws \MWException cascading {@see \BootstrapComponents\ApplicationFactory} calls
	 * @throws \ConfigException cascading {@see \Config::get}
	 *
	 * @return array
	 */
	public function initializeApplications( $myConfig ) {
		$applicationFactory = ApplicationFactory::getInstance();
		$componentLibrary = $applicationFactory->getComponentLibrary(
			$myConfig->get( 'BootstrapComponentsWhitelist' )
		);
		$nestingController = $applicationFactory->getNestingController();
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
	 * @param array $hookList
	 *
	 * @return int
	 */
	public function register( $hookList ) {
		foreach ( $hookList as $hook => $callback ) {
			Hooks::register( $hook, $callback );
		}
		return count( $hookList );
	}

	/**
	 * @param \ConfigFactory $configFactory
	 * Registers my own configuration, so that it is present during onLoad. See phabricator issue T184837
	 *
	 * @see https://phabricator.wikimedia.org/T184837
	 */
	public function registerMyConfiguration( $configFactory ) {
		$configFactory->register( 'BootstrapComponents', 'GlobalVarConfig::newInstance' );
	}

	/**
	 * Executes the setup process.
	 *
	 * @throws \ConfigException
	 *
	 * @return int
	 */
	public function run() {
		$requestedHooks = $this->compileRequestedHooksListFor(
			$this->myConfig
		);
		$hookCallbackList = $this->buildHookCallbackListFor(
			$requestedHooks
		);

		return $this->register( $hookCallbackList );
	}

	/**
	 * @throws \MWException
	 */
	private function assertExtensionBootstrapPresent() {
		if ( !defined( 'BS_VERSION' ) ) {
			echo 'The BootstrapComponents extension requires Extension Bootstrap to be installed. '
				. 'Please check <a href="https://github.com/oetterer/BootstrapComponents/">the online help</a>' . PHP_EOL;
			throw new MWException( 'BootstrapComponents needs extension Bootstrap present.' );
		}
	}

	/**
	 * @param ComponentLibrary  $componentLibrary
	 * @param NestingController $nestingController
	 *
	 * @return \Closure
	 */
	private function createParserFirstCallInitCallback( $componentLibrary, $nestingController ) {

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
						'BootstrapComponents',
						'Unknown handler type (' . $componentLibrary->getHandlerTypeFor( $componentName )
							. ') detected for component ' . $parserHookString
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
	private function createParserHookCallbackFor( $componentName, $componentLibrary, $nestingController, $parserOutputHelper ) {

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
	 * Version number retrieved from extension info array.
	 *
	 * @param string $version
	 */
	private function prepareEnvironment( $version ) {
		@define( 'BOOTSTRAP_COMPONENTS_VERSION', (string) $version );
	}
	### attend before deployment
	# mandatory
	#@todo add integration tests for placeTrackingCategory and invalid tracking category

	### last steps
	#@todo test in-wiki every component with every attribute, don't forget image options for components, image modal and carousel
	#@todo change release date in docs/release-notes.md
	#@todo remove the rest of the comments here. put ### this remains somewhere to keep track of things
	#@todo create composer package. see https://packagist.org/ and https://packagist.org/about#how-to-update-packages; packet name "bootstrap-components"
	#@todo put on github with automatic testing and scrutinizing


	# code improvement
	#@todo introduce header alternative for heading
	#@todo you can increase code coverage by testing private and protected methods directly
	# see https://jtreminio.com/2013/03/unit-testing-tutorial-part-3-testing-protected-private-methods-coverage-reports-and-crap/
	# when starting to use this, revert some previously exposed methods to protected/private again.


	### this remains
	#@todo when dropping support for mw > 1.31, replace manual class autoloading in extension.json with psr-4 autoloading
	#@todo add extensions requirement to extension.json for "Bootstrap": "~ 1.2" as soon as Bootstrap supports new Extension loading (leaving this in breaks 1.31.x)
	#@todo remove \BootstrapComponents\Setup::registerMyConfiguration when dropping support for mw > 1.31 (assuming T184837 will be fixed)
	#@todo components like popover, collapse, etc use #button to activate. You can supply an img tag and have an image inside the button. what if you want to have just the image?
	#@todo in case there are translations from translate wiki, augment /docs/credits with
	# Translations have been provided by the members of the [Translatewiki.net project](https://translatewiki.net).
}