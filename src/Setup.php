<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents;

use Bootstrap\BootstrapManager;
use \Hooks;
use \MediaWiki\MediaWikiServices;
use \Parser;

/**
 * Class Setup
 *
 * Registers all hooks and components for Extension BootstrapComponents
 *
 * @package BootstrapComponents
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
	 * @param ComponentLibrary  $componentLibrary
	 * @param NestingController $nestingController
	 *
	 * @return \Closure
	 */
	public function createParserFirstCallInitCallback( $componentLibrary, $nestingController ) {
		return function( Parser $parser ) use ( $componentLibrary, $nestingController ) {
			$componentFunctionFactory = ApplicationFactory::getInstance()
				->getComponentFunctionFactory( $parser, $componentLibrary, $nestingController );
			foreach ( $componentFunctionFactory->generateParserHookList() as $register ) {

				list ( $idTag, $handlerType, $callback ) = $register;

				if ( $handlerType == ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION ) {
					$parser->setFunctionHook( $idTag, $callback );
				} elseif ( $handlerType == ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION ) {
					$parser->setHook( $idTag, $callback );
				} else {
					wfDebugLog( 'BootstrapComponents', 'Unknown handler type (' . $handlerType . ') detected for component ' . $idTag );
				}
			}
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
			'ParserFirstCallInit'    => $this->createParserFirstCallInitCallback( $componentLibrary, $nestingController ),
			'SetupAfterCache'        => function() {
				BootstrapManager::getInstance()->addAllBootstrapModules();
				return true;
			},
		];

		if ( $myConfig->has( 'BootstrapComponentsEnableCarouselGalleryMode' )
			&& $myConfig->get( 'BootstrapComponentsEnableCarouselGalleryMode' )
		) {
			$hooks['GalleryGetModes'] = function( &$modeArray ) {
				$modeArray['carousel'] = 'BootstrapComponents\\CarouselGallery';
				return true;
			};
		}
		if ( $myConfig->has( 'BootstrapComponentsModalReplaceImageThumbnail' )
			&& $myConfig->get( 'BootstrapComponentsModalReplaceImageThumbnail' )
		) {
			$hooks['ImageBeforeProduceHTML'] = function(
				&$dummy, &$title, &$file, &$frameParams, &$handlerParams, &$time, &$res
			) use ( $nestingController ) {
				$imageModal = new ImageModal( $dummy, $title, $file, $nestingController );
				return $imageModal->parse( $frameParams, $handlerParams, $time, $res );
			};
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
			$myConfig->get('BootstrapComponentsDisableIdsForTestsEnvironment')
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
	#@fixme tests/parser/parserTests.txt
	#@todo adapt file headers (see chameleon and bootstrap for example)
	#@todo move the complex, consisting of instance self static and the getInstance() methods from the instances into the ApplicationFactory.
	#@todo ComponentLibrary::isParserFunction and ::isParserTag are scarcely used. remove or see to more usage
	#@todo remove newlines in image modal's image caption
	#@todo give \BootstrapComponents\ComponentFunctionFactory::__construct a parserOutputHelper directly, instead of a $parser


	### this remains
	#@todo when dropping support for mw > 1.31, replace manual class autoloading in extension.json with psr-4 autoloading
	#@todo add extensions requirement to extension.json for "Bootstrap": "~ 1.2" as soon as Bootstrap supports new Extension loading (leaving this in breaks 1.31.x)
	#@todo remove \BootstrapComponents\Setup::registerMyConfiguration when dropping support for mw > 1.31 (assuming T184837 will be fixed)
}