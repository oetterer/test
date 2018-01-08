<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\AttributeManager;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\AttributeManager
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  oetterer
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
				'heading', 'help', 'id', 'link', 'placement', 'size', 'style', 'text', 'trigger',
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
	 * @param string $description
	 *
	 * @dataProvider descriptionProvider
	 */
	public function testGetDescriptionFor( $attribute, $description ) {
		$instance = new AttributeManager();
		$this->assertEquals(
			$description,
			$instance->getDescriptionFor( $attribute )
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
			'help'        => [ 'help', false ],
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
	public function descriptionProvider() {
		return [
			'active'      => [ 'active', '&lt;bootstrap-components-attribute-active-description&gt;' ],
			'class'       => [ 'class', '&lt;bootstrap-components-attribute-class-description&gt;' ],
			'color'       => [ 'color', '&lt;bootstrap-components-attribute-color-description&gt;' ],
			'collapsible' => [ 'collapsible', '&lt;bootstrap-components-attribute-collapsible-description&gt;' ],
			'disabled'    => [ 'disabled', '&lt;bootstrap-components-attribute-disabled-description&gt;' ],
			'dismissible' => [ 'dismissible', '&lt;bootstrap-components-attribute-dismissible-description&gt;' ],
			'footer'      => [ 'footer', '&lt;bootstrap-components-attribute-footer-description&gt;' ],
			'heading'     => [ 'heading', '&lt;bootstrap-components-attribute-heading-description&gt;' ],
			'help'        => [ 'help', '&lt;bootstrap-components-attribute-help-description&gt;' ],
			'id'          => [ 'id', '&lt;bootstrap-components-attribute-id-description&gt;' ],
			'link'        => [ 'link', '&lt;bootstrap-components-attribute-link-description&gt;' ],
			'placement'   => [ 'placement', '&lt;bootstrap-components-attribute-placement-description&gt;' ],
			'size'        => [ 'size', '&lt;bootstrap-components-attribute-size-description&gt;' ],
			'style'       => [ 'style', '&lt;bootstrap-components-attribute-style-description&gt;' ],
			'text'        => [ 'text', '&lt;bootstrap-components-attribute-text-description&gt;' ],
			'trigger'     => [ 'trigger', '&lt;bootstrap-components-attribute-trigger-description&gt;' ],
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
			'help_fail'   => [ 'help', [ '1', 'true', true, false, [] ], false ],
			'rnd_fail'    => [ md5( microtime() ), [ md5( microtime() ) ], false ],
		];
	}
}
