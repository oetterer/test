<?php
/**
 * Contains the class controlling the references and creating necessary helper objects.
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

use \MWException;
use \ReflectionClass;

/**
 * Class ApplicationFactory
 *
 * Manages access to application classes.
 *
 * @since 1.0
 */
class ApplicationFactory {

	/**
	 * @var ApplicationFactory $instance
	 */
	private static $instance = null;

	/**
	 * Holds the application singletons
	 * @var array $applicationStore
	 */
	private $applicationStore;

	/**
	 * Library, that tells the ApplicationFactory, which class to use to instantiate which application
	 * @var array $applicationClassRegister
	 */
	private $applicationClassRegister;

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
	 * ApplicationFactory constructor.
	 *
	 * Do not instantiate directly, but use {@see ApplicationFactory::getInstance}
	 * instead.
	 *
	 * @see ApplicationFactory::getInstance
	 */
	public function __construct() {
		$this->applicationStore = [];
		$this->applicationClassRegister = $this->getApplicationClassRegister();
	}

	/**
	 * @throws MWException  cascading {@see \BootstrapComponents\ApplicationFactory::getApplication}
	 *
	 * @return AttributeManager
	 */
	public function getAttributeManager() {
		return $this->getApplication( 'AttributeManager' );
	}

	/**
	 * @param null|bool|array $componentWhiteList
	 *
	 * @throws MWException  cascading {@see \BootstrapComponents\ApplicationFactory::getApplication}
	 *
	 * @return ComponentLibrary
	 */
	public function getComponentLibrary( $componentWhiteList = null ) {
		return $this->getApplication( 'ComponentLibrary', $componentWhiteList );
	}

	/**
	 * @param string $id
	 * @param string $trigger must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 * @param string $content must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 *
	 * @return ModalBuilder
	 */
	public function getModalBuilder( $id, $trigger, $content ) {
		return new ModalBuilder( $id, $trigger, $content );
	}

	/**
	 * @param bool $disableUniqueIds    needed in parser tests
	 *
	 * @throws MWException  cascading {@see \BootstrapComponents\ApplicationFactory::getApplication}
	 *
	 * @return NestingController
	 */
	public function getNestingController( $disableUniqueIds = false ) {
		return $this->getApplication( 'NestingController', $disableUniqueIds );
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

	/**
	 * @param \Parser $parser
	 *
	 * @throws MWException  cascading {@see \BootstrapComponents\ApplicationFactory::getApplication}
	 *
	 * @return ParserOutputHelper
	 */
	public function getParserOutputHelper( $parser = null ) {
		if ( $parser === null ) {
			$parser = $GLOBALS['wgParser'];
		}
		return $this->getApplication( 'ParserOutputHelper', $parser );
	}

	/**
	 * Registers an application with the ApplicationFactory.
	 * @param string $name
	 * @param string $class
	 *
	 * @throws MWException when class to register does not exist
	 *
	 * @return bool
	 */
	public function registerApplication( $name, $class ) {
		$application = trim( $name );
		$applicationClass = trim( $class );
		if ( $application != '' && class_exists( $applicationClass ) ) {
			$this->applicationClassRegister[$application] = $applicationClass;
			return true;
		} elseif( $application != '' ) {
			throw new MWException( 'ApplicationFactory was requested to register non existing class "' . $applicationClass . '"!');
		}
		wfDebugLog(
			'BootstrapComponents',
			'BootstrapComponents\\ApplicationFactory: Trying to register invalid application for class ' . $applicationClass . '!'
		);
		return false;
	}

	/**
	 * This returns the application $name. Creates a new instance and stores the singleton, if not already in store.
	 * You can supply any number of additional arguments to this function, they will be passed to the constructor.
	 *
	 * @param string $name
	 *
	 * @return mixed|object
	 * @throws MWException  when no class is registered for the requested application
	 */
	protected function getApplication( $name ) {
		if ( isset( $this->applicationStore[$name] ) ) {
			return $this->applicationStore[$name];
		}
		if ( !isset( $this->applicationClassRegister[$name] ) ) {
			throw new MWException( 'ApplicationFactory was requested to return application "' . $name . '". No appropriate class registered!');
		}
		$args = func_get_args();
		array_shift( $args ); # because, we already used the first argument $name

		$objectReflection = new ReflectionClass( $this->applicationClassRegister[$name] );
		return $this->applicationStore[$name] = $objectReflection->newInstanceArgs( $args );
	}

	/**
	 * Resets the application $application (or all, if $application is null), so that the next call to
	 * {@see \BootstrapComponents\ApplicationFactory::getApplication} will create a new object.
	 *
	 * @param null|string $application
	 *
	 * @return bool
	 */
	public function resetLookup( $application = null ) {
		if ( is_null( $application ) ) {
			$this->applicationStore = [];
			return true;
		} elseif( isset( $this->applicationStore[$application] ) ) {
			unset( $this->applicationStore[$application] );
			return true;
		}
		return false;
	}

	/**
	 * @return array
	 */
	private function getApplicationClassRegister() {
		return [
			'AttributeManager' => 'BootstrapComponents\\AttributeManager',
			'ComponentLibrary' => 'BootstrapComponents\\ComponentLibrary',
			'NestingController' => 'BootstrapComponents\\NestingController',
			'ParserOutputHelper' => 'BootstrapComponents\\ParserOutputHelper',
		];
	}
}