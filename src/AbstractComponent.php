<?php
/**
 * Contains base class for all components.
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

/**
 * Class Component
 *
 * Abstract class for all component classes
 *
 * @since 1.0
 */
abstract class AbstractComponent implements NestableInterface {
	/**
	 * Holds a reference of the application's attribute manger.
	 * Can be used to verify the provided attributes or attribute values.
	 *
	 * @var AttributeManager $attributeManager
	 */
	private static $attributeManager = null;

	/**
	 * @var ComponentLibrary $componentLibrary
	 */
	private $componentLibrary;

	/**
	 * The (html) id of this component. Not available before the component was opened.
	 *
	 * @var string $id
	 */
	private $id;

	/**
	 * Name of the component
	 *
	 * @var string $name
	 */
	private $name;

	/**
	 * @var NestingController $nestingController
	 */
	private $nestingController;

	/**
	 * @var NestableInterface|false $parentComponent
	 */
	private $parentComponent;

	/**
	 * @var ParserOutputHelper $parserOutputHelper
	 */
	private $parserOutputHelper;

	/**
	 * @var ParserRequest $parserRequest
	 */
	private $parserRequest;

	/**
	 * For every of my registered attributes holds a value. false, if not valid in supplied
	 * parserRequest.
	 *
	 * @var array $sanitizedAttributes
	 */
	private $sanitizedAttributes;

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
	 * @param ComponentLibrary   $componentLibrary
	 * @param ParserOutputHelper $parserOutputHelper
	 * @param NestingController  $nestingController
	 *
	 * @throws MWException cascading {@see \BootstrapComponents\ComponentLibrary::getNameFor}
	 *                      or {@see \BootstrapComponents\Component::extractAttribute}
	 */
	public function __construct( $componentLibrary, $parserOutputHelper, $nestingController ) {
		$this->componentLibrary = $componentLibrary;
		$this->parserOutputHelper = $parserOutputHelper;
		$this->nestingController = $nestingController;
		if ( !self::$attributeManager ) {
			self::$attributeManager = ApplicationFactory::getInstance()->getAttributeManager();
		}
		$this->name = $componentLibrary->getNameFor(
			get_class( $this )
		);
		$this->setParentComponent(
			$this->getNestingController()->getCurrentElement()
		);
		$this->parserRequest = false;
		$this->sanitizedAttributes = [];
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
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param ParserRequest $parserRequest ;
	 *
	 * @throws \MWException self or cascading from {@see \BootstrapComponents\Component::processArguments}
	 *                      or {@see \BootstrapComponents\NestingController::close}
	 * @return string|array
	 */
	public function parseComponent( $parserRequest ) {
		if ( !is_a( $parserRequest, 'BootstrapComponents\ParserRequest' )  ) {
			throw new MWException( 'Invalid ParserRequest supplied to component ' . $this->getComponentName() . '! Got class ' . get_class( $parserRequest ) );
		}
		$this->parserRequest = $parserRequest;
		$this->setId(
			$this->getValueFor( 'id' ) !== false
				? $this->getValueFor( 'id' )
				: $this->getNestingController()->generateUniqueId( $this->getComponentName() )
		);
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
	 * Returns the classes reference to the component library.
	 *
	 * @return AttributeManager
	 */
	protected function getAttributeManager() {
		return self::$attributeManager;
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
	 * @return NestableInterface|false
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
	 * @return ParserRequest
	 */
	protected function getParserRequest() {
		return $this->parserRequest;
	}

	/**
	 * @param string      $attribute
	 * @param bool|string $fallback
	 *
	 * @return bool|string
	 */
	protected function getValueFor( $attribute, $fallback = false ) {
		if ( !isset( $this->sanitizedAttributes[$attribute] ) ) {
			$this->sanitizedAttributes[$attribute] = $this->sanitizeAttribute(
				$attribute,
				$this->getParserRequest()->getAttributes(),
				$this->getParserRequest()->getParser()
			);
		}
		return $this->sanitizedAttributes[$attribute] === false ? $fallback : $this->sanitizedAttributes[$attribute];
	}

	/**
	 * Takes your class and style string and appends them with corresponding data from user (if present)
	 * passed in attributes.
	 *
	 * @param string|array $class
	 * @param string|array $style
	 *
	 * @return array[] containing (array)$class and (array)$style
	 */
	protected function processCss( $class, $style  ) {
		if ( !is_array( $class ) ) {
			$class = [ $class ];
		}
		if ( !is_array( $style ) ) {
			$style = [ $style ];
		}
		if ( $newClass = $this->getValueFor( 'class' ) ) {
			$class[] = $newClass;
		}
		if ( $newStyle = $this->getValueFor( 'style' ) ) {
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
	 * @param string   $attribute
	 * @param string[] $attributes
	 * @param \Parser  $parser
	 *
	 * @return bool|string
	 */
	private function sanitizeAttribute( $attribute, $attributes, $parser ) {
		if ( !isset( $attributes[$attribute] ) ) {
			return false;
		}
		$value = $attributes[$attribute];
		if ( is_string( $value ) ) {
			$value = $parser->recursiveTagParse( $value );
		}
		if ( $this->getAttributeManager()->verifyValueFor( $attribute, $value ) ) {
			return $value;
		}
		return false;
	}

	/**
	 * @param string $id
	 */
	private function setId( $id ) {
		if ( is_string( $id ) ) {
			$this->id = $id;
		}
	}

	/**
	 * @param NestableInterface|false $parentComponent
	 */
	private function setParentComponent( $parentComponent ) {
		$this->parentComponent = $parentComponent;
	}
}