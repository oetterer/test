<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\AbstractComponent;
use BootstrapComponents\ComponentLibrary;
use \MWException;
use \PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers  \BootstrapComponents\AbstractComponent
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
class AbstractComponentTest extends ComponentsTestBase {
	private $componentPlacing = '<component placing>';

	private $name = 'abstract';

	/**
	 * @return AbstractComponent|PHPUnit_Framework_MockObject_MockObject
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
			'BootstrapComponents\\AbstractComponent',
			[ $componentLibrary, $this->getParserOutputHelper(), $this->getNestingController() ]
		);
		$stub->expects( $this->any() )
			->method( 'placeMe' )
			->will( $this->returnValue( $this->componentPlacing ) );
		return $stub;
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'BootstrapComponents\\AbstractComponent',
			$this->createStub()
		);
		$this->assertInstanceOf(
			'BootstrapComponents\\NestableInterface',
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
	public function testSimpleOutput( $component ) {
		$parserRequest = $this->buildParserRequest(
			'',
			[ 'class' => 'test-class' ]
		);
		$class = $this->getComponentLibrary()->getClassFor( $component );
		/** @var AbstractComponent $instance */
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
			'/class="[^"]*test-class"/',
			$parsedString
		);
	}

	/**
	 * @return array
	 */
	public function allComponentsProvider() {
		return [
			'accordion' => [ 'accordion' ],
			'alert'     => [ 'alert' ],
			'collapse'  => [ 'collapse' ],
			'jumbotron' => [ 'jumbotron' ],
			'modal'     => [ 'modal' ],
			'panel'     => [ 'panel' ],
			'well'      => [ 'well' ],
		];
	}
}
