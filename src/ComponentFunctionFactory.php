<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents;

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
	 * @param \Parser           $parser
	 * @param ComponentLibrary  $componentLibrary
	 * @param NestingController $nestingController
	 *
	 * @throws \MWException cascading the application calls to {@see \BootstrapComponents\ApplicationFactory}
	 */
	public function __construct( $parser, $componentLibrary, $nestingController ) {
		$this->parserOutputHelper = ApplicationFactory::getInstance()->getParserOutputHelper( $parser );
		$this->componentLibrary = $componentLibrary;
		$this->nestingController = $nestingController;
	}

	/**
	 * Creates the function to be called by the parser when encountering the component while processing.
	 *
	 * @param string             $componentName
	 * @param ComponentLibrary   $componentLibrary
	 * @param ParserOutputHelper $parserOutputHelper
	 * @param NestingController  $nestingController
	 *
	 * @return \Closure
	 */
	public function createHookFunctionFor( $componentName, $componentLibrary, $parserOutputHelper, $nestingController ) {
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
		$componentLibrary = $this->getComponentLibrary();
		$parserOutputHelper = $this->getParserOutputHelper();
		$nestingController = $this->getNestingController();
		foreach ( $this->getComponentLibrary()->getRegisteredComponents() as $componentName ) {
			$ParserHookList[] = [
				self::PARSER_HOOK_PREFIX . strtolower( $componentName ),
				$componentLibrary->getHandlerTypeFor( $componentName ),
				$this->createHookFunctionFor( $componentName, $componentLibrary, $parserOutputHelper, $nestingController ),
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