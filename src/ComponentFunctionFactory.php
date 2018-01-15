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
	 * ComponentFunctionFactory constructor.
	 *
	 * @param Parser $parser
	 */
	public function __construct( Parser $parser ) {
		$applicationFactory = ApplicationFactory::getInstance();
		$this->componentLibrary = $applicationFactory->getComponentLibrary();
		$this->parserOutputHelper = $applicationFactory->getParserOutputHelper( $parser );
		$this->nestingController = $applicationFactory->getNestingController();
	}

	/**
	 * Creates the function to be called by the parser when encountering the component while processing.
	 *
	 * @param string $componentName
	 *
	 * @return Closure
	 */
	public function createHookFunctionFor( $componentName ) {
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
	 * @return array[] each item holding parserHook, handlerType, callback function
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
	 * @return ComponentLibrary
	 */
	protected function getComponentLibrary() {
		return $this->componentLibrary;
	}

	/**
	 * @return NestingController
	 */
	protected function getNestingController() {
		return $this->nestingController;
	}

	/**
	 * @return ParserOutputHelper
	 */
	protected function getParserOutputHelper() {
		return $this->parserOutputHelper;
	}
}