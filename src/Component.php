<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents;

use BootstrapComponents\Component\Panel;
use \MWException;

/**
 * Class Component
 *
 * Abstract class for all component classes
 *
 * @package BootstrapComponents
 */
abstract class Component implements Nestable {
	/**
	 * Holds a reference of the application's attribute manger.
	 * Can be used to verify the provided attributes or attribute values.
	 *
	 * @var AttributeManager
	 */
	private static $attributeManager = null;

	/**
	 * @var ComponentLibrary
	 */
	private $componentLibrary;

	/**
	 * The (html) id of this component. Not available before the component was opened.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Name of the component
	 *
	 * @var string
	 */
	private $name;

	/**
	 * @var NestingController
	 */
	private $nestingController;

	/**
	 * @var Nestable|false
	 */
	private $parentComponent;

	/**
	 * @var ParserOutputHelper
	 */
	private $parserOutputHelper;

	/**
	 * Does the actual work in the individual components.
	 *
	 * @param ParserRequest $parserRequest
	 *
	 * @return string|array
	 */
	abstract protected function placeMe( $parserRequest );

	/**
	 * Component constructor.
	 *
	 * Takes care of
	 * - calculation component id
	 *
	 * @param ComponentLibrary   $componentLibrary
	 * @param ParserOutputHelper $parserOutputHelper
	 * @param NestingController  $nestingController
	 *
	 * @throws MWException cascading {@see \BootstrapComponents\ComponentLibrary::getNameFor} or {@see \BootstrapComponents\Component::extractAttribute}
	 */
	public function __construct( $componentLibrary, $parserOutputHelper, $nestingController ) {
		$this->componentLibrary = $componentLibrary;
		$this->parserOutputHelper = $parserOutputHelper;
		$this->nestingController = $nestingController;
		$this->name = $componentLibrary->getNameFor(
			get_class( $this )
		);
		$this->setParentComponent(
			$this->getNestingController()->getCurrentElement()
		);
	}

	/**
	 * Returns the name of the component.
	 *
	 * @return string
	 */
	public function getComponentName() {
		return $this->name;
	}

	/**
	 * Note that id is only present, if component requested one (in constructor) and the component has been opened in
	 * {@see \BootstrapComponents\Component::renderComponent}.
	 *
	 * @return null|string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param ParserRequest $parserRequest ;
	 *
	 * @throws \MWException cascading from {@see \BootstrapComponents\Component::processArguments}
	 *  or {@see \BootstrapComponents\NestingController::close}
	 * @return string|array
	 */
	public function parseComponent( $parserRequest ) {
		$this->setId(
			$this->checkForManualIdIn( $parserRequest )
		);
		if ( $this->getId() === null ) {
			$this->setId(
				$this->getNestingController()->generateUniqueId( $this->getComponentName() )
			);
		}
		$this->augmentParserOutput();

		$this->getNestingController()->open( $this );
		$ret = $this->placeMe( $parserRequest );
		$this->getNestingController()->close( $this->getId() );
		return $ret;
	}

	/**
	 * Converts the input array to a string using glue. Removes invalid (false) entries beforehand.
	 *
	 * @param array|false $array
	 * @param string      $glue
	 *
	 * @return false|string returns false on empty array, string otherwise
	 */
	protected function arrayToString( $array, $glue ) {
		if ( empty( $array ) ) {
			return false;
		}
		foreach ( (array) $array as $key => $item ) {
			if ( $item === false || $item === '' ) {
				unset( $array[$key] );
			}
		}
		return count( $array ) ? implode( $glue, $array ) : false;
	}

	/**
	 * If $attribute is set in $attributes and is valid, it is returned. Otherwise default is returned
	 *
	 * @param string $attribute
	 * @param array  $attributes
	 * @param mixed  $default
	 *
	 * @throws MWException cascading {@see \BootstrapComponents\ComponentLibrary::getAttributesFor}
	 * @return string|false|null
	 */
	protected function extractAttribute( $attribute, $attributes, $default = false ) {
		if ( in_array( $attribute, $this->getComponentLibrary()->getAttributesFor( $this->getComponentName() ) )
			&& is_array( $attributes )
			&& isset( $attributes[$attribute] )
			&& strlen( trim( $attributes[$attribute] ) )
			&& $this->getAttributeManager()->verifyValueFor( $attribute, trim( $attributes[$attribute] ) )
		) {
			return trim( $attributes[$attribute] );
		}
		return $default;
	}

	/**
	 * Returns the classes reference to the component library.
	 *
	 * @return AttributeManager
	 */
	protected function getAttributeManager() {
		if ( self::$attributeManager ) {
			return self::$attributeManager;
		}
		return self::$attributeManager = ApplicationFactory::getInstance()->getAttributeManager();
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
	 * @return Nestable|false
	 */
	protected function getParentComponent() {
		return $this->parentComponent;
	}

	/**
	 * @return ParserOutputHelper
	 */
	protected function getParserOutputHelper() {
		return $this->parserOutputHelper;
	}

	/**
	 * Takes your class and style string and appends them with corresponding data from user (if present)
	 * passed in attributes.
	 *
	 * @param string|array $class
	 * @param string|array $style
	 * @param array        $attributes
	 *
	 * @throws MWException cascading {@see \BootstrapComponents\Component::extractAttribute}
	 * @return array[] containing (array)$class and (array)$style
	 */
	protected function processCss( $class, $style, $attributes ) {
		if ( !is_array( $class ) ) {
			$class = [ $class ];
		}
		if ( !is_array( $style ) ) {
			$style = [ $style ];
		}
		if ( $newClass = $this->extractAttribute( 'class', $attributes ) ) {
			$class[] = $newClass;
		}
		if ( $newStyle = $this->extractAttribute( 'style', $attributes ) ) {
			$style[] = $newStyle;
		}
		return [ $class, $style ];
	}

	/**
	 * Performs all the mandatory actions on the parser output for the component class
	 */
	private function augmentParserOutput() {
		$this->getParserOutputHelper()->addTrackingCategory();
		$this->getParserOutputHelper()->loadBootstrapModules();
		$modules = $this->getComponentLibrary()->getModulesFor(
			$this->getComponentName(),
			$this->getParserOutputHelper()->getNameOfActiveSkin()
		);
		$this->getParserOutputHelper()->addModules( $modules );
	}

	/**
	 * @param ParserRequest $parserRequest
	 *
	 * @throws MWException cascading {@see \BootstrapComponents\Component::extractAttribute}
	 * @return string|null
	 */
	private function checkForManualIdIn( ParserRequest $parserRequest ) {
		$attributes = $parserRequest->getAttributes();
		return $this->extractAttribute( 'id', $attributes, null );
	}

	/**
	 * @param string|null $id
	 */
	private function setId( $id ) {
		$this->id = $id;
	}

	/**
	 * @param Nestable|false $parentComponent
	 */
	private function setParentComponent( $parentComponent ) {
		$this->parentComponent = $parentComponent;
	}
}