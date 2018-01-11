<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents;

use \MWException;

/**
 * Class ComponentLibrary
 *
 * Holds information about all registered components
 *
 * @package BootstrapComponents
 */
class ComponentLibrary {
	/**
	 * @var array
	 */
	const DEFAULT_ATTRIBUTES = [ 'class', 'id', 'style' ];

	/**
	 * @var string
	 */
	const HANDLER_TYPE_PARSER_FUNCTION = 'ParserFunction';

	/**
	 * @var string
	 */
	const HANDLER_TYPE_TAG_EXTENSION = 'TagExtension';

	/**
	 * @var ComponentLibrary
	 */
	private static $instance = null;

	/**
	 * This array holds all the data for all known components, whether they are registered or not.
	 *
	 * Array has form
	 * <pre>
	 *  "componentName" => [
	 *      "class" => <className>,
	 *      "handlerType" => <handlerType>,
	 *      "attributes" => [ "attr1", "attr2", ... ],
	 *      "modules" => [
	 *          "default" => [ "module1", "module2", ... ],
	 *          "<skin>" => [ "module1", "module2", ... ],
	 *      ]
	 *  ]
	 * </pre>
	 *
	 * @var array
	 */
	private $componentDataStore;

	/**
	 * Array that maps a class name to the corresponding component name
	 *
	 * @var array
	 */
	private $componentNamesByClass;

	/**
	 * If array, holds the names of the component that are allowed through config. If bool is true for all and false for none.
	 *
	 * @var bool|array
	 */
	private $componentWhiteList;

	/**
	 * The list of available bootstrap components
	 *
	 * @var string[]
	 */
	private $registeredComponents;

	/**
	 * Returns the singleton instance
	 *
	 * @param bool|array $componentWhiteList
	 *
	 * @return ComponentLibrary
	 */
	public static function getInstance( $componentWhiteList = null ) {
		if ( self::$instance !== null ) {
			return self::$instance;
		}
		return self::$instance = new self( $componentWhiteList );
	}

	/**
	 * ComponentLibrary constructor.
	 *
	 * @param bool|array $componentWhiteList (see {@see \BootstrapComponents\ComponentLibrary::$componentWhiteList})
	 */
	public function __construct( $componentWhiteList = true ) {

		if ( is_null( $componentWhiteList ) ) {
			#@todo integration test: check if wgBootstrapComponentsWhitelist is already set
			$componentWhiteList = true;
		}
		$this->componentWhiteList = $componentWhiteList;
		list ( $this->registeredComponents, $this->componentNamesByClass, $this->componentDataStore )
			= $this->registerComponents( $this->componentWhiteList );
	}

	/**
	 * Compiles an array for all bootstrap component parser functions to be uses in the i18n.magic file
	 *
	 * @return array
	 */
	public function compileMagicWordsArray() {
		$magicWords = [];
		foreach ( $this->getRegisteredComponents() as $componentName ) {
			if ( $this->isParserFunction( $componentName ) ) {
				$magicWords[ComponentFunctionFactory::PARSER_HOOK_PREFIX . $componentName]
					= [ 0, ComponentFunctionFactory::PARSER_HOOK_PREFIX . $componentName ];
			}
		}
		return $magicWords;
	}

	/**
	 * Checks, if component '$component' is registered with the tag manager
	 *
	 * @param string $component
	 *
	 * @return bool
	 */
	public function componentIsRegistered( $component ) {
		return in_array( $component, $this->registeredComponents );
	}

	/**
	 * @param string $component
	 *
	 * @return bool|array
	 * @throws MWException provided component is not known
	 */
	public function getAttributesFor( $component ) {
		if ( !isset( $this->componentDataStore[$component] ) ) {
			throw new MWException( 'Trying to get attribute list for unknown component "' . (string) $component . '"!' );
		}
		return $this->componentDataStore[$component]['attributes'];
	}

	/**
	 * Returns class name for a registered component
	 *
	 * @param string $componentName
	 *
	 * @throws MWException provided component is not registered
	 * @return string
	 */
	public function getClassFor( $componentName ) {
		if ( !$this->componentIsRegistered( $componentName ) ) {
			throw new MWException( 'Trying to get a class for an unregistered component "' . (string) $componentName . '"!' );
		}
		return $this->componentDataStore[$componentName]['class'];
	}

	/**
	 * Returns handler type for a registered component. 'UNKNOWN' for unknown components.
	 *
	 * @see \BootstrapComponents\ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION, \BootstrapComponents\ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION
	 *
	 * @param string $component
	 *
	 * @return string
	 */
	public function getHandlerTypeFor( $component ) {
		if ( !isset( $this->componentDataStore[$component] ) ) {
			return 'UNKNOWN';
		}
		return $this->componentDataStore[$component]['handlerType'];
	}

	/**
	 * Returns an array of all the known component's names.
	 *
	 * @return array
	 */
	public function getKnownComponents() {
		return array_keys( $this->componentDataStore );
	}

	/**
	 * Returns all the needed modules for a registered component. False, if there are none.
	 * If skin is set, returns all modules especially registered for that skin as well
	 *
	 * @param string $componentName
	 * @param string $skin
	 *
	 * @return array
	 */
	public function getModulesFor( $componentName, $skin = null ) {
		$modules = isset( $this->componentDataStore[$componentName]['modules']['default'] )
			? (array) $this->componentDataStore[$componentName]['modules']['default']
			: [];
		if ( !$skin || !isset( $this->componentDataStore[$componentName]['modules'][$skin] ) ) {
			return $modules;
		}
		return (array) array_merge(
			$modules,
			(array) $this->componentDataStore[$componentName]['modules'][$skin]
		);
	}

	/**
	 * Returns the component name for a given class
	 *
	 * @param string $componentClass
	 *
	 * @throws MWException if supplied class is not registered
	 * @return string
	 */
	public function getNameFor( $componentClass ) {
		if ( !isset( $this->componentNamesByClass[$componentClass] ) ) {
			throw new MWException( 'Trying to get a component name for unregistered class "' . (string) $componentClass . '"!' );
		}
		return $this->componentNamesByClass[$componentClass];
	}

	/**
	 * Returns an array of all the registered component's names.
	 *
	 * @return string[]
	 */
	public function getRegisteredComponents() {
		return $this->registeredComponents;
	}

	/**
	 * True, if referenced component is marked as parser function
	 *
	 * @param string $componentName
	 *
	 * @return bool
	 */
	public function isParserFunction( $componentName ) {
		return $this->getHandlerTypeFor( $componentName ) == self::HANDLER_TYPE_PARSER_FUNCTION;
	}

	/**
	 * True, if referenced component is marked as tag extension
	 *
	 * @param string $componentName
	 *
	 * @return bool
	 */
	public function isTagExtension( $componentName ) {
		return $this->getHandlerTypeFor( $componentName ) == self::HANDLER_TYPE_TAG_EXTENSION;
	}

	/**
	 * Generates the array for registered components containing all whitelisted components and the two supporting data arrays.
	 *
	 * @param bool|array $componentWhiteList
	 *
	 * @return array[] $registeredComponents, $componentNamesByClass, $componentDataStore
	 */
	private function registerComponents( $componentWhiteList ) {
		$componentDataStore = [];
		$componentNamesByClass = [];
		$registeredComponents = [];
		foreach ( $this->rawComponentsDefinition() as $componentName => $componentData ) {
			$componentData['attributes'] = (array)$componentData['attributes'];
			if ( $componentData['attributes']['default'] ) {
				$componentData['attributes'] = array_unique( array_merge( $componentData['attributes'], self::DEFAULT_ATTRIBUTES ) );
			}
			unset( $componentData['attributes']['default'] );

			$componentDataStore[$componentName] = $componentData;
			if ( !$componentWhiteList || (is_array( $componentWhiteList ) && !in_array( $componentName, $componentWhiteList )) ) {
				// if $componentWhiteList is false, or and array and does not contain the componentName, we will not register it
				continue;
			}
			$registeredComponents[] = $componentName;
			$componentNamesByClass[$componentData['class']] = $componentName;
		}

		return [ $registeredComponents, $componentNamesByClass, $componentDataStore ];
	}

	/**
	 * Raw library data used in registration process.
	 *
	 * @return array
	 */
	private function rawComponentsDefinition() {
		return [
			'accordion' => [
				'class'       => 'BootstrapComponents\\Component\\Accordion',
				'handlerType' => self::HANDLER_TYPE_TAG_EXTENSION,
				'attributes'  => [
					'default' => true,
				],
			],
			'alert'     => [
				'class'       => 'BootstrapComponents\\Component\\Alert',
				'handlerType' => self::HANDLER_TYPE_TAG_EXTENSION,
				'attributes'  => [
					'default' => true,
					'color',
					'dismissible',
				],
			],
			'badge'     => [
				'class'       => 'BootstrapComponents\\Component\\Badge',
				'handlerType' => self::HANDLER_TYPE_PARSER_FUNCTION,
				'attributes'  => [
					'default' => true,
				],
			],
			'button'    => [
				'class'       => 'BootstrapComponents\\Component\\Button',
				'handlerType' => self::HANDLER_TYPE_PARSER_FUNCTION,
				'attributes'  => [
					'default' => true,
					'active',
					'disabled',
					'color',
					'size',
					'text',
				],
				'modules'     => [
					'vector' => 'ext.bootstrapComponents.button.vector-fix',
				],
			],
			'carousel'  => [
				'class'       => 'BootstrapComponents\\Component\\Carousel',
				'handlerType' => self::HANDLER_TYPE_PARSER_FUNCTION,
				'attributes'  => [
					'default' => true,
				],
				'modules'     => [
					'default' => 'ext.bootstrapComponents.carousel.fix',
				],
			],
			'collapse'  => [
				'class'       => 'BootstrapComponents\\Component\\Collapse',
				'handlerType' => self::HANDLER_TYPE_TAG_EXTENSION,
				'attributes'  => [
					'default' => true,
					'active',
					'disabled',
					'color',
					'size',
					'text',
				],
				'modules'     => [
					'vector' => 'ext.bootstrapComponents.button.vector-fix',
				],
			],
			'icon'      => [
				'class'       => 'BootstrapComponents\\Component\\Icon',
				'handlerType' => self::HANDLER_TYPE_PARSER_FUNCTION,
				'attributes'  => [
					'default' => false,
				],
			],
			'jumbotron' => [
				'class'       => 'BootstrapComponents\\Component\\Jumbotron',
				'handlerType' => self::HANDLER_TYPE_TAG_EXTENSION,
				'attributes'  => [
					'default' => true,
				],
			],
			'label'     => [
				'class'       => 'BootstrapComponents\\Component\\Label',
				'handlerType' => self::HANDLER_TYPE_PARSER_FUNCTION,
				'attributes'  => [
					'default' => true,
					'color',
				],
			],
			'modal'     => [
				'class'       => 'BootstrapComponents\\Component\\Modal',
				'handlerType' => self::HANDLER_TYPE_TAG_EXTENSION,
				'attributes'  => [
					'default' => true,
					'color',
					'footer',
					'heading',
					'size',
					'text',
				],
				'modules'     => [
					'default' => 'ext.bootstrapComponents.modal.fix',
					'vector'  => [
						'ext.bootstrapComponents.button.vector-fix',
						'ext.bootstrapComponents.modal.vector-fix',
					],
				],
			],
			'panel'     => [
				'class'       => 'BootstrapComponents\\Component\\Panel',
				'handlerType' => self::HANDLER_TYPE_TAG_EXTENSION,
				'attributes'  => [
					'active',
					'collapsible',
					'color',
					'default' => true,
					'footer',
					'heading',
				],
			],
			'popover'   => [
				'class'       => 'BootstrapComponents\\Component\\Popover',
				'handlerType' => self::HANDLER_TYPE_TAG_EXTENSION,
				'attributes'  => [
					'color',
					'default' => true,
					'heading',
					'placement',
					'size',
					'text',
					'trigger',
				],
				'modules'     => [
					'default' => 'ext.bootstrapComponents.popover',
					'vector'  => [
						'ext.bootstrapComponents.button.vector-fix',
						'ext.bootstrapComponents.popover.vector-fix',
					],
				],
			],
			'tooltip'   => [
				'class'       => 'BootstrapComponents\\Component\\Tooltip',
				'handlerType' => self::HANDLER_TYPE_PARSER_FUNCTION,
				'attributes'  => [
					'default' => true,
					'placement',
					'text',
				],
				'modules'     => [
					'default' => 'ext.bootstrapComponents.tooltip',
				],
			],
			'well'      => [
				'class'       => 'BootstrapComponents\\Component\\Well',
				'handlerType' => self::HANDLER_TYPE_TAG_EXTENSION,
				'attributes'  => [
					'default' => true,
					'size',
				],
			],
		];
	}
}