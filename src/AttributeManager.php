<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents;

/**
 * Class TagManager
 *
 * Manages the execution of the <bootstrap> tag
 *
 * @package BootstrapComponents
 */
class AttributeManager {

	/**
	 * Holds the register for allowed attributes per component
	 * @var array[]
	 */
	private $allowedValuesForAttribute = [];

	/**
	 * AttributeManager constructor.
	 */
	public function __construct() {
		$this->allowedValuesForAttribute = [];
		$this->register( 'active', true );
		$this->register( 'class', true );
		$this->register( 'color', [ 'default', 'primary', 'success', 'info', 'warning', 'danger' ] );
		$this->register( 'collapsible', true );
		$this->register( 'disabled', true );
		$this->register( 'dismissible', true );
		$this->register( 'footer', true );
		$this->register( 'heading', true );
		$this->register( 'id', true );
		$this->register( 'link', true );
		$this->register( 'placement', [ 'top', 'bottom', 'left', 'right' ] );
		$this->register( 'size', [ 'xs', 'sm', 'md', 'lg' ] );
		$this->register( 'style', true );
		$this->register( 'text', true );
		$this->register( 'trigger', [ 'focus', 'hover' ] );
	}

	/**
	 * Returns the list of all available attributes
	 *
	 * @return array
	 */
	public function getAllAttributes() {
		return array_keys( $this->allowedValuesForAttribute );
	}

	/**
	 * Returns the allowed values for a given attribute or NULL if invalid attribute
	 * Note that allowed values can be true (for any) or false (for none)
	 *
	 * @param string $attribute
	 *
	 * @return null|array|bool
	 */
	public function getAllowedValuesFor( $attribute ) {
		if ( !$this->registered( $attribute ) ) {
			return null;
		}
		return $this->allowedValuesForAttribute[$attribute];
	}

	/**
	 * Checks if given `attribute` is registered with the manager
	 *
	 * @param string $attribute
	 *
	 * @return bool
	 */
	public function registered( $attribute ) {
		return isset( $this->allowedValuesForAttribute[$attribute] );
	}

	/**
	 * For a given attribute, this verifies, if value is allowed.
	 *
	 * Note that every value for an unregistered attribute fails verification automatically
	 *
	 * @param string $attribute
	 * @param string $value
	 *
	 * @return bool
	 */
	public function verifyValueFor( $attribute, $value ) {
		if ( !is_string( $value ) ) {
			return false;
		}
		$allowedValues = $this->getAllowedValuesFor( $attribute );
		if ( !$allowedValues ) {
			return false;
		}
		if ( is_bool( $allowedValues ) ) {
			// $allowedValues is bool and not false => is true, meaning all values are allowed
			return true;
		}
		// $allowedValues can be null, bool or an array. since all them have been handled above, except the array, we can do:
		return in_array( $value, $allowedValues );
	}

	/**
	 * Registers `attribute` with a description and allowed values
	 *
	 * @param string     $attribute
	 * @param array|bool $allowedValues
	 */
	private function register( $attribute, $allowedValues ) {
		$this->allowedValuesForAttribute[$attribute] = $allowedValues;
	}
}