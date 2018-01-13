<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents;

use Bootstrap\BootstrapManager;
use \Closure;
use \Config;
use \ConfigException;
use \ConfigFactory;
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
	 * @throws ConfigException cascading {@see \ConfigFactory::makeConfig}
	 *
	 * @return bool
	 */
	public static function onExtensionLoad( $info ) {
		$setup = new self();
		if ( !empty( $info ) ) {
			$setup->prepareEnvironment();
		}
		$setup->bootstrapExtensionPresent();
		$setup->registerMyConfiguration(
			MediaWikiServices::getInstance()->getConfigFactory()
		);

		$setup->registerHooks(
			MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'BootstrapComponents' )
		);
		return true;
	}

	/**
	 * @return Closure
	 */
	public function createParserFirstCallInitCallback() {
		return function( Parser $parser ) {
			$componentFunctionFactory = ApplicationFactory::getInstance()
				->getComponentFunctionFactory( $parser );
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
	 * @param string $hook
	 *
	 * @return boolean
	 */
	public function isRegistered( $hook ) {
		return Hooks::isRegistered( $hook );
	}

	/**
	 * Defines all hooks and the corresponding callbacks
	 *
	 * @param  Config $myConfig
	 *
	 * @throws \ConfigException cascading {@see \Config::get}
	 *
	 * @return Closure[]
	 */
	public function getHooksToRegister( $myConfig ) {
		$hooks = [
			'ParserFirstCallInit'    => $this->createParserFirstCallInitCallback(),
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
			) {
				$imageModal = new ImageModal( $dummy, $title, $file );
				return $imageModal->parse( $frameParams, $handlerParams, $time, $res );
			};
		}

		return $hooks;
	}

	/**
	 * Does the actual registration of hooks and components
	 *
	 * @param  Config $myConfig
	 *
	 * @throws \ConfigException cascading {@see \BootstrapComponents\Setup::getHooksToRegister}
	 */
	public function registerHooks( $myConfig ) {
		foreach ( $this->getHooksToRegister( $myConfig ) as $hook => $callback ) {
			Hooks::register( $hook, $callback );
		}
	}

	/**
	 * @param ConfigFactory $configFactory
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

	private function prepareEnvironment() {
		define( 'BOOTSTRAP_COMPONENTS_VERSION', '1.0.0' );
	}
	# attend before deployment
	#@todo get code quality above 9
	#@todo adapt file headers (see chameleon and bootstrap for example)
	#@todo introduce integration test; require-dev smw seems the easiest way to do this. decide, if working with 3.0.0 or 2.5.(4|5)
	#   or use parser tests instead. see https://www.mediawiki.org/wiki/Parser_tests
	#@todo put on github with automatic testing and scrutinizing
	#@todo create composer package. see https://packagist.org/ and https://packagist.org/about#how-to-update-packages; packet name "bootstrap-components"
	#@todo add more comments
	#@todo recheck code for https://www.mediawiki.org/wiki/Security_checklist_for_developers#Dynamic_code_generation > Any user input: no isset!
	#@todo complete documentation in /doc (installation, configuration, howto, expansion)
	#@todo ComponentLibrary::isParserFunction and ::isParserTag are scarcely used. remove or see to more usage
	#@todo in ImageModalTest.php we have long running tests. All that use origThumbAndModalTriggerCompareAllCaseProvider (even the single)
	#@todo revamp config access:
	# see \BootstrapComponents\ImageModal::generateTriggerCalculateImageWidth how to access global config
	# use $myConfig = \MediaWiki\MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'BootstrapComponents' ); $myConfig->get( 'BootstrapComponentsWhitelist' ) );


	# this remains
	#@todo when switching to 1.31, replace manual class autoloading in extension.json to psr-4 autoloading
	#@todo add extensions requirement to extension.json for "Bootstrap": "~ 1.2" as soon as Bootstrap supports new Extension loading (leaving this in breaks 1.31.x)
	#@todo remove \BootstrapComponents\Setup::registerMyConfiguration when dropping support for 1.27
}