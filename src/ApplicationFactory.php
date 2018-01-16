<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents;

use \Config;
use \MWException;
use \Parser;
use \ReflectionClass;

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
	 * Holds the application singletons
	 * @var array
	 */
	private $applicationStore;

	/**
	 * Library, that tells the ApplicationFactory, which class to use to instantiate which application
	 * @var array
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
	 * @param Parser $parser
	 * @param Config $myConfig
	 *
	 * @throws MWException  cascading {@see \BootstrapComponents\ApplicationFactory::getApplication}
	 *
	 * @return ComponentFunctionFactory
	 */
	public function getComponentFunctionFactory( Parser $parser = null, $myConfig = null ) {
		if ( $parser === null ) {
			$parser = $GLOBALS['wgParser'];
		}
		return $this->getApplication( 'ComponentFunctionFactory', $parser, $myConfig );
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
	 * @param Parser $parser
	 *
	 * @throws MWException  cascading {@see \BootstrapComponents\ApplicationFactory::getApplication}
	 *
	 * @return ParserOutputHelper
	 */
	public function getParserOutputHelper( Parser $parser = null ) {
		if ( $parser === null ) {
			$parser = $GLOBALS['wgParser'];
		}
		return $this->getApplication( 'ParserOutputHelper', $parser );
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
	 * Registers an application with the ApplicationFactory.
	 * @param string $name
	 * @param string $class
	 *
	 * @throws MWException when class to register does not exist
	 */
	protected function registerApplication( $name, $class ) {
		$application = trim( $name );
		$applicationClass = trim( $class );
		if ( $application != '' && class_exists( $applicationClass ) ) {
			$this->applicationClassRegister[$application] = $applicationClass;
		} elseif( $application != '' ) {
			throw new MWException( 'ApplicationFactory was requested to register non existing class "' . $applicationClass . '"!');
		}
		wfDebugLog(
			'BootstrapComponents',
			'BootstrapComponents\\ApplicationFactory: Trying to register invalid application for class ' . $applicationClass . '!'
		);
	}

	/**
	 * Resets the application $application (or all, if $application is null), so that the next call to
	 * {@see \BootstrapComponents\ApplicationFactory::getApplication} will create a new object.
	 *
	 * @param null|string $application
	 */
	protected function resetLookup( $application = null ) {
		if ( is_null( $application ) ) {
			$this->applicationStore = [];
		} elseif( isset( $this->applicationStore[$application] ) ) {
			unset( $this->applicationStore[$application] );
		}
	}

	/**
	 * @return array
	 */
	private function getApplicationClassRegister() {
		return [
			'AttributeManager' => 'BootstrapComponents\\AttributeManager',
			'ComponentFunctionFactory' => 'BootstrapComponents\\ComponentFunctionFactory',
			'ComponentLibrary' => 'BootstrapComponents\\ComponentLibrary',
			'NestingController' => 'BootstrapComponents\\NestingController',
			'ParserOutputHelper' => 'BootstrapComponents\\ParserOutputHelper',
		];
	}
}