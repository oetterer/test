<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\AttributeManager;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\AttributeManager
 *
 * @ingroup Test
 *
 * @group extension-bootstrap-components
 * @group mediawiki-databaseless
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
	 * @param string $attribute
	 * @param array  $valuesToTest
	 * @param bool   $expectedVerificationResult
	 *
	 * @dataProvider verifyValueProvider
	 */
	public function testVerifyValueFor( $attribute, $valuesToTest, $expectedVerificationResult ) {
		$instance = new AttributeManager();
		foreach ( $valuesToTest as $value ) {
			$verificationResult = $instance->verifyValueFor( $attribute, $value );
			$this->assertEquals(
				$verificationResult,
				$expectedVerificationResult
			);
			$this->assertInternalType(
				'boolean',
				$expectedVerificationResult
			);
		}
	}

	/**
	 * @return array[]
	 */
	public function allowedValuesForAttributeProvider() {
		return [
			'active'      => [ 'active', true ],
			'class'       => [ 'class', true ],
			'color'       => [ 'color', [ 'default', 'primary', 'success', 'info', 'warning', 'danger' ] ],
			'collapsible' => [ 'collapsible', true ],
			'disabled'    => [ 'disabled', true ],
			'dismissible' => [ 'dismissible', true ],
			'footer'      => [ 'footer', true ],
			'heading'     => [ 'heading', true ],
			'id'          => [ 'id', true ],
			'link'        => [ 'link', true ],
			'placement'   => [ 'placement', [ 'top', 'bottom', 'left', 'right' ] ],
			'size'        => [ 'size', [ 'xs', 'sm', 'md', 'lg' ] ],
			'style'       => [ 'style', true ],
			'text'        => [ 'text', true ],
			'trigger'     => [ 'trigger', [ 'focus', 'hover' ] ],
			'rnd'         => [ md5( microtime() ), null ],
		];
	}

	/**
	 * @return array[]
	 */
	public function verifyValueProvider() {
		return [
			'active'      => [ 'active', [ md5( microtime() ), md5( microtime() . microtime() ) ], true ],
			'active_fail' => [ 'active', [ true, false, null, [] ], false ],
			'color'       => [ 'color', [ 'default', 'primary', 'success', 'info', 'warning', 'danger' ], true ],
			'color_fail'  => [ 'color', [ '!default', true, false, null, [] ], false ],
			'rnd_fail'    => [ md5( microtime() ), [ md5( microtime() ) ], false ],
		];
	}
}
