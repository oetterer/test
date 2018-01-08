<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents;

use \Parser;

/**
 * Class ApplicationFactory
 *
 * Manages access to application classes.
 *
 * @package BootstrapComponents
 */
class ApplicationFactory {
	/**
	 * @var ApplicationFactory
	 */
	private static $instance = null;

	/**
	 * Returns the singleton instance
	 *
	 * @return ApplicationFactory
	 */
	public static function getInstance() {
		if ( self::$instance !== null ) {
			return self::$instance;
		}

		return self::$instance = new self();
	}

	/**
	 * @return AttributeManager
	 */
	public function getAttributeManager() {
		return AttributeManager::getInstance();
	}

	/**
	 * @param Parser $parser
	 * @param array  $configuration
	 *
	 * @return ComponentFunctionFactory
	 */
	public function getComponentFunctionFactory( Parser $parser = null, $configuration = null ) {
		if ( $parser === null ) {
			$parser = $GLOBALS['wgParser'];
		}
		if ( $configuration === null ) {
			$configuration = $GLOBALS;
		}
		return ComponentFunctionFactory::getInstance( $parser, $configuration );
	}

	/**
	 * @param null|bool|array $componentWhiteList
	 *
	 * @return ComponentLibrary
	 */
	public function getComponentLibrary( $componentWhiteList = null ) {
		return ComponentLibrary::getInstance( $componentWhiteList );
	}

	/**
	 * @return NestingController
	 */
	public function getNestingController() {
		return NestingController::getInstance();
	}

	/**
	 * @param Parser $parser
	 *
	 * @return ParserOutputHelper
	 */
	public function getParserOutputHelper( Parser $parser = null ) {
		if ( !$parser ) {
			$parser = $GLOBALS['wgParser'];
		}
		return ParserOutputHelper::getInstance( $parser );
	}

	/**
	 * @param array  $argumentsPassedByParser
	 * @param string $handlerType
	 *
	 * @return ParserRequest
	 */
	public function getNewParserRequest( array $argumentsPassedByParser, $handlerType = ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION ) {
		return new ParserRequest( $argumentsPassedByParser, $handlerType );
	}
}