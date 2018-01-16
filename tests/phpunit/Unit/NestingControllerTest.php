<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\Component;
use BootstrapComponents\ComponentLibrary;
use BootstrapComponents\NestingController;
use \MWException;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\NestingController
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer
 */
class NestingControllerTest extends PHPUnit_Framework_TestCase {
	/**
	 * @param string $componentName
	 * @param string $componentClass
	 *
	 * @return Component
	 */
	private function getComponent( $componentName, $componentClass ) {
		$mock = $this->getMockBuilder( $componentClass )
			->disableOriginalConstructor()
			->getMock();
		$mock->expects( $this->any() )
			->method( 'getId' )
			->willReturn( 'mockId_' . $componentName . '_' . md5( microtime() ) );

		/** @var Component $mock */
		return $mock;
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'BootstrapComponents\\NestingController',
			new NestingController( false )
		);
	}

	/**
	 * @param string $componentName
	 * @param string $componentClass
	 *
	 * @dataProvider componentNameAndClassProvider
	 * @throws
	 */
	public function testCanOpenAndClose( $componentName, $componentClass ) {
		$instance = new NestingController( false );
		$this->assertEquals(
			0,
			$instance->getStackSize()
		);
		$component = $this->getComponent( $componentName, $componentClass );
		/** @noinspection PhpParamsInspection */
		$instance->open( $component );
		$this->assertEquals(
			1,
			$instance->getStackSize()
		);
		$this->assertInstanceOf(
			'BootstrapComponents\\Component',
			$instance->getCurrentElement()
		);
		$this->assertEquals(
			$component->getId(),
			$instance->getCurrentElement()->getId()
		);
		/** @noinspection PhpParamsInspection */
		$instance->close(
		/** @var Component $component */
			$component->getId()
		);
		$this->assertEquals(
			0,
			$instance->getStackSize()
		);
	}

	/**
	 * @throws MWException
	 */
	public function testCloseFailOnEmptyStack() {
		$instance = new NestingController( false );

		$this->setExpectedException( 'MWException' );

		$instance->close( 'invalid' );
	}

	/**
	 * @throws MWException
	 */
	public function testCloseFailOnInvalidId() {
		$instance = new NestingController( false );

		$this->setExpectedException( 'MWException' );

		/** @var Component $component */
		$component = $this->getComponent( 'panel', 'BootstrapComponents\\Component\\Panel' );
		$instance->open( $component );

		$instance->close( 'invalid' );
	}

	public function testCanGenerateUniqueId() {
		$instance = new NestingController( false );
		foreach ( $this->uniqueIdProvider() as $testParams ) {
			$this->doTestCanGenerateUniqueId( $instance, $testParams );
		}
	}


	public function testCanGenerateStaticIdForTests() {
		$instance = new NestingController( true );
		foreach ( $this->uniqueIdProvider() as $testParams ) {
			list( $componentName,) = $testParams;
			$this->assertEquals(
				'bsc_' . $componentName . '_test',
				$instance->generateUniqueId( $componentName )
			);
		}
	}

	/**
	 * @param NestingController $instance
	 * @param string[]          $testParams
	 */
	private function doTestCanGenerateUniqueId( $instance, $testParams ) {
		list( $componentName, $expectedId ) = $testParams;
		$this->assertEquals(
			$expectedId,
			$instance->generateUniqueId( $componentName )
		);
	}

	/**
	 * @return array[]
	 * @throws MWException
	 */
	public function componentNameAndClassProvider() {
		$cl = new ComponentLibrary();
		$provider = [];
		foreach ( $cl->getRegisteredComponents() as $componentName ) {
			$provider['open ' . $componentName] = [ $componentName, $cl->getClassFor( $componentName ) ];
		}
		return $provider;
	}

	/**
	 * is used in a foreach, not as dataProvider per se
	 *
	 * @return array
	 */
	public function uniqueIdProvider() {
		return [
			[ 'alert', 'bsc_alert_0' ],
			[ 'alert', 'bsc_alert_1' ],
			[ 'alert', 'bsc_alert_2' ],
			[ 'button', 'bsc_button_0' ],
			[ 'button', 'bsc_button_1' ],
			[ 'well', 'bsc_well_0' ],
			[ 'button', 'bsc_button_2' ],
			[ 'alert', 'bsc_alert_3' ],
			[ 'alert', 'bsc_alert_4' ],
		];
	}
}
