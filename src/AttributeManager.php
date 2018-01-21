<?php
/**
 * Contains the class for handling component attributes/parameters.
 *
 * @copyright (C) 2018, Tobias Oetterer, Paderborn University
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

/**
 * Class TagManager
 *
 * Manages the execution of the <bootstrap> tag
 *
 * @since 1.0
 */
class AttributeManager {

	const NO_FALSE_VALUE = 0;
	const ANY_VALUE = 1;

	/**
	 * Holds the register for allowed attributes per component
	 *
	 * @var array $allowedValuesForAttribute
	 */
	private $allowedValuesForAttribute;

	/**
	 * Holds all values indicating a "no". Can be used to ignore "enable"-fields.
	 *
	 * @var array $noValues
	 */
	private $noValues;

	/**
	 * AttributeManager constructor.
	 *
	 * Do not instantiate directly, but use {@see ApplicationFactory::getAttributeManager}
	 * instead.
	 *
	 * @see ApplicationFactory::getAttributeManager
	 */
	public function __construct() {
		$this->allowedValuesForAttribute = $this->getInitialAttributeRegister();
		$this->noValues = [ false, 0, '0', 'no', 'false', 'off', 'disabled', 'ignored' ];
		$this->noValues[] = strtolower( wfMessage( 'confirmable-no' )->text() );
	}

	/**
	 * Returns the list of all available attributes
	 *
	 * @return string[]
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
		if ( !$this->isRegistered( $attribute ) ) {
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
	public function isRegistered( $attribute ) {
		return isset( $this->allowedValuesForAttribute[$attribute] );
	}

	/**
	 * Registers `attribute` with a description and allowed values
	 *
	 * notes on attribute registering:
	 * * AttributeManager::ANY_VALUE: every non empty string is allowed
	 * * AttributeManager::NO_FALSE_VALUE: as along as the attribute is present and NOT set to a value contained in $this->noValues,
	 *      the attribute is considered valid. note that flag attributes will be set to the empty string, thus having <tag active></tag> will have
	 *      active set to "". Handle accordingly in your component. See {@see \BootstrapComponents\Component\Button::calculateClassFrom} for example.
	 * * array: attribute must be present and contain a value in the array to be valid
	 *
	 * Note also, that values will be converted to lower case before checking, you therefore should only put lower case values in your
	 * allowed-values array.
	 *
	 * @param string     $attribute
	 * @param array|int  $allowedValues
	 *
	 * @return bool
	 */
	public function register( $attribute, $allowedValues ) {
		if ( !is_string( $attribute ) || !strlen( trim( $attribute ) ) ) {
			return false;
		}
		if ( !is_int( $allowedValues ) && ( !is_array( $allowedValues ) || !count( $allowedValues ) ) ) {
			return false;
		}
		$this->allowedValuesForAttribute[trim( $attribute )] = $allowedValues;
		return true;
	}

	/**
	 * For a given attribute, this verifies, if value is allowed. If verification succeeds, lowercase value will be returned, false otherwise.
	 * If an attribute is registered as NO_FALSE_VALUE and value is the empty string, it gets converted to true.
	 *
	 * Note that every value for an unregistered attribute fails verification automatically
	 *
	 * @param string $attribute
	 * @param string $value
	 *
	 * @return bool|string
	 */
	public function verifyValueForAttribute( $attribute, $value ) {
		$allowedValues = $this->getAllowedValuesFor( $attribute );
		if ( $allowedValues === self::NO_FALSE_VALUE ) {
			return $this->verifyValueForNoValueAttribute( $value );
		} elseif ( $allowedValues === self::ANY_VALUE ) {
			return $value;
		}
		$value = strtolower( $value );
		if ( is_array( $allowedValues ) && in_array( $value, $allowedValues, true ) ) {
			return $value;
		}
		return false;
	}

	/**
	 * @return array
	 */
	private function getInitialAttributeRegister() {
		return [
			'active'      => self::NO_FALSE_VALUE,
			'class'       => self::ANY_VALUE,
			'color'       => [ 'default', 'primary', 'success', 'info', 'warning', 'danger' ],
			'collapsible' => self::NO_FALSE_VALUE,
			'disabled'    => self::NO_FALSE_VALUE,
			'dismissible' => self::NO_FALSE_VALUE,
			'footer'      => self::ANY_VALUE,
			'heading'     => self::ANY_VALUE,
			'id'          => self::ANY_VALUE,
			'link'        => self::ANY_VALUE,
			'placement'   => [ 'top', 'bottom', 'left', 'right' ],
			'size'        => [ 'xs', 'sm', 'md', 'lg' ],
			'style'       => self::ANY_VALUE,
			'text'        => self::ANY_VALUE,
			'trigger'     => [ 'default', 'focus', 'hover' ],
		];

	}

	/**
	 * @param string $value
	 *
	 * @return bool|string
	 */
	private function verifyValueForNoValueAttribute( $value ) {
		$value = strtolower( $value );
		if ( !in_array( $value, $this->noValues, true ) ) {
			return empty( $value ) ? true : $value;
		}
		return false;
	}
}