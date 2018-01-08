<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\Component;
use BootstrapComponents\ComponentLibrary;
use \MWException;
use \PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers  \BootstrapComponents\Component
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  oetterer
 */
class ComponentTest extends ComponentsTestBase {
	private $componentPlacing = '<component placing>';

	private $name = 'abstract';

	/**
	 * @return Component|PHPUnit_Framework_MockObject_MockObject
	 */
	private function createStub() {
		$componentLibrary = $this->getMockBuilder( 'BootstrapComponents\\ComponentLibrary' )
			->disableOriginalConstructor()
			->getMock();
		$componentLibrary->expects( $this->any() )
			->method( 'getNameFor' )
			->will( $this->returnValue( $this->name ) );
		$componentLibrary->expects( $this->any() )
			->method( 'getAttributesFor' )
			->will( $this->returnValue( [] ) );

		$stub = $this->getMockForAbstractClass(
			'BootstrapComponents\\Component',
			[ $componentLibrary, $this->getParserOutputHelper(), $this->getNestingController() ]
		);
		$stub->expects( $this->any() )
			->method( 'placeMe' )
			->will( $this->returnValue( $this->componentPlacing ) );
		return $stub;
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'BootstrapComponents\\Component',
			$this->createStub()
		);
		$this->assertInstanceOf(
			'BootstrapComponents\\Nestable',
			$this->createStub()
		);
	}

	public function testGetId() {
		$id = $this->createStub()->getId();

		$this->assertEquals(
			null,
			$id
		);
	}

	/**
	 * @throws MWException
	 */
	public function testParseComponent() {
		$parserRequest = $this->buildParserRequest(
			'',
			[]
		);
		/** @noinspection PhpParamsInspection */
		$parsedString = $this->createStub()->parseComponent(
			$parserRequest
		);

		$this->assertEquals(
			$this->componentPlacing,
			$parsedString
		);
	}

	/**
	 * @param string $component
	 *
	 * @throws MWException
	 * @dataProvider allComponentsProvider
	 */
	public function testProduceHelp( $component ) {
		$parserRequest = $this->buildParserRequest(
			'',
			[ 'help' => true ]
		);
		$class = $this->getComponentLibrary()->getClassFor( $component );
		/** @var Component $instance */
		$instance = new $class(
			$this->getComponentLibrary(),
			$this->getParserOutputHelper(),
			$this->getNestingController()
		);
		/** @noinspection PhpParamsInspection */
		$parsedString = $instance->parseComponent(
			$parserRequest
		);
		$this->assertInternalType( 'string', $parsedString );
		$this->assertRegExp(
			'/id="help_for_' . $component . '"/',
			$parsedString
		);
	}

	/**
	 * @return array
	 */
	public function allComponentsProvider() {
		$data = [];
		foreach ( (new ComponentLibrary())->getKnownComponents() as $component ) {
			$data[$component] = [ $component ];
		}
		return $data;
	}
}
