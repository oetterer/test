<?php
/**
 * Contains the class producing the function called by the parser for rendering a component.
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

use \ReflectionClass;

/**
 * Class ComponentFunctionFactory
 *
 * Spawns a component objects
 *
 * @since 1.0
 */
class ComponentFunctionFactory {
	const PARSER_HOOK_PREFIX = 'bootstrap_';

	/**
	 * @var ComponentLibrary $componentLibrary
	 */
	private $componentLibrary;

	/**
	 * @var NestingController $nestingController
	 */
	private $nestingController;

	/**
	 * @var ParserOutputHelper $parserOutputHelper
	 */
	private $parserOutputHelper;

	/**
	 * ComponentFunctionFactory constructor.
	 *
	 * Do not instantiate directly, but use {@see ApplicationFactory::getComponentFunctionFactory}
	 * instead.
	 *
	 * @param \Parser           $parser
	 * @param ComponentLibrary  $componentLibrary
	 * @param NestingController $nestingController
	 *
	 * @see ApplicationFactory::getComponentFunctionFactory
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
			/** @var AbstractComponent $object */
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