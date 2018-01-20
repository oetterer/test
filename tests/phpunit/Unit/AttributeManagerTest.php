<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\AttributeManager;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\AttributeManager
 *
 * @ingroup Test
 *
 * @group   extension-bootstrap-components
 * @group   mediawiki-databaseless
 *
 * @license GNU GPL v3+
 *
 * @since   1.0
 * @author  Tobias Oetterer
 */
class AttributeManagerTest extends PHPUnit_Framework_TestCase {
	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\AttributeManager',
			new AttributeManager()
		);
	}

	public function testGetAllAttributes() {
		$instance = new AttributeManager();
		$this->assertEquals(
			[
				'active', 'class', 'color', 'collapsible', 'disabled', 'dismissible', 'footer',
				'heading', 'id', 'link', 'placement', 'size', 'style', 'text', 'trigger',
			],
			$instance->getAllAttributes()
		);
	}

	/**
	 * @param string $attribute
	 * @param array  $allowedValues
	 *
	 * @dataProvider allowedValuesForAttributeProvider
	 */
	public function testGetAllowedValuesFor( $attribute, $allowedValues ) {
		$instance = new AttributeManager();
		$this->assertEquals(
			$allowedValues,
			$instance->getAllowedValuesFor( $attribute )
		);
	}

	/**
	 * @param string    $newAttribute
	 * @param int|array $allowedValue
	 *
	 * @dataProvider canRegisterNewAttributesProver
	 */
	public function testCanRegisterNewAttributes( $newAttribute, $allowedValue ) {
		$instance = new AttributeManager();
		$this->assertTrue(
			!$instance->isRegistered( $newAttribute )
		);
		$this->assertTrue(
			$instance->register( $newAttribute, $allowedValue )
		);
		$this->assertTrue(
			$instance->isRegistered( $newAttribute )
		);
		$this->assertEquals(
			$allowedValue,
			$instance->getAllowedValuesFor( $newAttribute )
		);
	}

	public function testFailRegister() {
		$instance = new AttributeManager();
		$this->assertTrue(
			!$instance->register( "", 1 )
		);
		$this->assertTrue(
			!$instance->register( false, 1 )
		);
		$this->assertTrue(
			!$instance->register( 'empty_array_fail', [] )
		);
	}

	/**
	 * @param string $attribute
	 * @param array  $valuesToTest
	 *
	 * @dataProvider verifyValueProvider
	 */
	public function testVerifyValueFor( $attribute, $valuesToTest ) {
		$instance = new AttributeManager();
		foreach ( $valuesToTest as $value ) {
			$verificationResult = $instance->verifyValueFor( $attribute, $value );
			$this->assertInternalType(
				'bool',
				$verificationResult
			);
			$this->assertTrue(
				$verificationResult,
				'failed with value (' . gettype( $value ) . ') ' . $value
			);
		}
	}

	/**
	 * @param string $attribute
	 * @param array  $valuesToTest
	 *
	 * @dataProvider failToVerifyValueProvider
	 */
	public function testFailToVerifyValueFor( $attribute, $valuesToTest ) {
		$instance = new AttributeManager();
		foreach ( $valuesToTest as $value ) {
			$verificationResult = $instance->verifyValueFor( $attribute, $value );
			$this->assertInternalType(
				'boolean',
				$verificationResult
			);
			$this->assertTrue(
				!$verificationResult,
				'failed with value (' . gettype( $value ) . ') ' . $value
			);
		}
	}

	/**
	 * @return array[]
	 */
	public function allowedValuesForAttributeProvider() {
		return [
			'active'      => [ 'active', false ],
			'class'       => [ 'class', true ],
			'color'       => [ 'color', [ 'default', 'primary', 'success', 'info', 'warning', 'danger' ] ],
			'collapsible' => [ 'collapsible', false ],
			'disabled'    => [ 'disabled', false ],
			'dismissible' => [ 'dismissible', false ],
			'footer'      => [ 'footer', true ],
			'heading'     => [ 'heading', true ],
			'id'          => [ 'id', true ],
			'link'        => [ 'link', true ],
			'placement'   => [ 'placement', [ 'top', 'bottom', 'left', 'right' ] ],
			'size'        => [ 'size', [ 'xs', 'sm', 'md', 'lg' ] ],
			'style'       => [ 'style', true ],
			'text'        => [ 'text', true ],
			'trigger'     => [ 'trigger', [ 'default', 'focus', 'hover' ] ],
			'rnd'         => [ md5( microtime() ), null ],
		];
	}

	/**
	 * @return array
	 */
	public function canRegisterNewAttributesProver() {
		return [
			'any_value' => [ 'any_value', AttributeManager::ANY_VALUE ],
			'no_value'  => [ 'no_value', AttributeManager::NO_FALSE_VALUE ],
			'array'     => [ 'array_value', [ 'yes', 'no' ] ],
		];
	}

	/**
	 * @return array[]
	 */
	public function verifyValueProvider() {
		return [
			'active' => [ 'active', [ md5( microtime() ), md5( microtime() . microtime() ) ] ],
			'class'  => [ 'class', [ md5( microtime() ), md5( microtime() . microtime() ) ] ],
			'color'  => [ 'color', [ 'default', 'primary', 'success', 'info', 'warning', 'danger' ] ],
		];
	}

	/**
	 * @return array[]
	 */
	public function failToVerifyValueProvider() {
		return [
			'active'      => [ 'active', [ 0, false, 'no', 'false', 'off', '0', 'disabled', 'ignored' ] ],
			'collapsible' => [ 'collapsible', [ 0, false, 'no', 'false', 'off', '0', 'disabled', 'ignored' ] ],
			'color'       => [ 'color', [ 0, false, 'no', 'false', 'off', '0', 'disabled', 'ignored' ] ],
			'disabled'    => [ 'disabled', [ 0, false, 'no', 'false', 'off', '0', 'disabled', 'ignored' ] ],
			'dismissible' => [ 'dismissible', [ 0, false, 'no', 'false', 'off', '0', 'disabled', 'ignored' ] ],
			'rnd_fail'    => [ md5( microtime() ), [ md5( microtime() ) ] ],
		];
	}
}
