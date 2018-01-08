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
use \Hooks;
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
	 * @return bool
	 */
	public static function onExtensionLoad() {
		$setup = new self();
		$setup->registerHooks( $GLOBALS );
		return true;
	}

	/**
	 * @param array $configuration
	 *
	 * @return Closure
	 */
	public function createParserFirstCallInitCallback( $configuration ) {
		return function( Parser $parser ) use ( $configuration ) {
			$componentFunctionFactory = ApplicationFactory::getInstance()
				->getComponentFunctionFactory( $parser, $configuration );
			foreach ( $componentFunctionFactory->generateParserHookList() as $register ) {

				list ( $idTag, $handlerType, $callback ) = $register;

				if ( $handlerType == ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION ) {
					$parser->setHook( $idTag, $callback );
				} else {
					$parser->setFunctionHook( $idTag, $callback );
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
	 * @param array $configuration
	 *
	 * @return Closure[]
	 */
	public function getHooksToRegister( $configuration ) {
		$hooks = [
			'ParserFirstCallInit'    => $this->createParserFirstCallInitCallback( $configuration ),
			'SetupAfterCache'        => function() {
				BootstrapManager::getInstance()->addAllBootstrapModules();
				return true;
			},
		];

		if ( isset( $configuration['wgBootstrapComponentsEnableCarouselGalleryMode'] )
			&& $configuration['wgBootstrapComponentsEnableCarouselGalleryMode']
		) {
			$hooks['GalleryGetModes'] = function( &$modeArray ) {
				$modeArray['carousel'] = 'BootstrapComponents\\CarouselGallery';
			};
		}
		if ( isset( $configuration['wgBootstrapComponentsModalReplaceImageThumbnail'] )
			&& $configuration['wgBootstrapComponentsModalReplaceImageThumbnail']
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
	 * @param array $configuration
	 */
	public function registerHooks( $configuration ) {
		foreach ( $this->getHooksToRegister( $configuration ) as $hook => $callback ) {
			Hooks::register( $hook, $callback );
		}
	}
	# attend before deployment
	#@todo adapt file headers (see chameleon and bootstrap for example)
	#@todo add english messages to i18n/en.json; OR: kick online help...
	#@todo introduce integration test; require-dev smw seems the easiest way to do this. decide, if working with 3.0.0 or 2.5.(4|5)
	#   or use parser tests instead. see https://www.mediawiki.org/wiki/Parser_tests
	#@todo put on github with automatic testing and scrutinizing
	#@todo create composer package. see https://packagist.org/ and https://packagist.org/about#how-to-update-packages; packet name "bootstrap-components"
	#@todo add more comments
	#@todo recheck code for https://www.mediawiki.org/wiki/Security_checklist_for_developers#Dynamic_code_generation > Any user input: no isset!
	#@todo complete documentation in /doc (installation, configuration, howto, expansion)

	# this remains
	#@todo when switching to 1.31, replace manual class autoloading in extension.json to psr-4 autoloading
}