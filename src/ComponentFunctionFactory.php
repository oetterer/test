<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents;

use \Closure;
use \MWException;
use \Parser;
use \ReflectionClass;

/**
 * Class ComponentFunctionFactory
 *
 * Spawns a component objects
 *
 * @package BootstrapComponents
 */
class ComponentFunctionFactory {
	const PARSER_HOOK_PREFIX = 'bootstrap_';

	/**
	 * @var ComponentFunctionFactory
	 */
	private static $instance = null;

	/**
	 * @var ComponentLibrary
	 */
	private $componentLibrary;

	/**
	 * @var NestingController
	 */
	private $nestingController;

	/**
	 * @var ParserOutputHelper
	 */
	private $parserOutputHelper;

	/**
	 * Returns the singleton instance
	 *
	 * @param Parser $parser
	 * @param array  $configuration
	 *
	 * @return ComponentFunctionFactory
	 */
	public static function getInstance( Parser $parser = null, $configuration = [] ) {
		if ( self::$instance !== null ) {
			return self::$instance;
		}

		return self::$instance = new self( $parser, $configuration );
	}

	/**
	 * ComponentFunctionFactory constructor.
	 *
	 * @param Parser $parser
	 * @param array  $configuration
	 */
	public function __construct( Parser $parser, $configuration = [] ) {
		$applicationFactory = ApplicationFactory::getInstance();
		$this->componentLibrary = $applicationFactory->getComponentLibrary(
			isset( $configuration['wgBootstrapComponentsWhitelist'] ) ? $configuration['wgBootstrapComponentsWhitelist'] : true
		);
		$this->parserOutputHelper = $applicationFactory->getParserOutputHelper( $parser );
		$this->nestingController = $applicationFactory->getNestingController();
	}

	/**
	 * @return array[] each item holding parserHook, handlerType, callback function
	 * @throws MWException
	 */
	public function generateParserHookList() {
		$ParserHookList = [];
		foreach ( $this->getComponentLibrary()->getRegisteredComponents() as $componentName ) {
			$ParserHookList[] = [
				self::PARSER_HOOK_PREFIX . strtolower( $componentName ),
				$this->getComponentLibrary()->getHandlerTypeFor( $componentName ),
				$this->createHookFunctionFor( $componentName ),
			];
		}
		return $ParserHookList;
	}

	/**
	 * Creates the function to be called by the parser when encountering the component while processing.
	 *
	 * @param string $componentName
	 *
	 * @return Closure
	 */
	private function createHookFunctionFor( $componentName ) {
		$componentLibrary = $this->getComponentLibrary();
		$nestingController = $this->getNestingController();
		$parserOutputHelper = $this->getParserOutputHelper();
		return function() use ( $componentName, $componentLibrary, $parserOutputHelper, $nestingController ) {
			$componentClass = $componentLibrary->getClassFor( $componentName );
			$objectReflection = new ReflectionClass( $componentClass );
			$object = $objectReflection->newInstanceArgs( [ $componentLibrary, $parserOutputHelper, $nestingController ] );

			$parserRequest = ApplicationFactory::getInstance()->getNewParserRequest(
				func_get_args(),
				$componentLibrary->getHandlerTypeFor( $componentName )
			);
			/** @var Component $object */
			return $object->parseComponent( $parserRequest );
		};
	}

	/**
	 * @return ComponentLibrary
	 */
	private function getComponentLibrary() {
		return $this->componentLibrary;
	}

	/**
	 * @return NestingController
	 */
	private function getNestingController() {
		return $this->nestingController;
	}

	/**
	 * @return ParserOutputHelper
	 */
	private function getParserOutputHelper() {
		return $this->parserOutputHelper;
	}
}