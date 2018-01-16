<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\ComponentFunctionFactory;
use BootstrapComponents\ComponentLibrary;
use \Parser;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\ComponentFunctionFactory
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer
 */
class ComponentFunctionFactoryTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var Parser
	 */
	private $parser;

	public function setUp() {
		parent::setUp();
		$this->parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\ComponentFunctionFactory',
			new ComponentFunctionFactory( $this->parser )
		);
	}

	public function testCanCreateHookFunctions() {
		$instance = new ComponentFunctionFactory( $this->parser );
		$parserHookList = $instance->generateParserHookList();

		$this->assertInternalType(
			'array',
			$parserHookList
		);
		// since we base all further tests on all the components having a representation
		// in the $parserHookList, we test, if the list has enough entries
		$this->assertEquals(
			14,
			count( $parserHookList )
		);
		foreach ( $parserHookList as $parserHookData ) {
			$this->doTestCanCreateHookFunctions( $parserHookData );
		}
	}

	/**
	 * @param string $componentName
	 *
	 * @dataProvider createHookFunctionProvider
	 */
	public function testCanCreateHookFunctionFor( $componentName ) {

		$componentLibrary = new ComponentLibrary( true );
		$instance = new ComponentFunctionFactory( $this->parser );

		$hookFunction = $instance->createHookFunctionFor( $componentName, $componentLibrary );

		$this->assertTrue(
			is_callable( $hookFunction )
		);

		$this->setExpectedException( 'MWException' );
		$hookFunction();
	}

	/**
	 * @return array
	 */
	public function createHookFunctionProvider() {
		$componentLibrary = new ComponentLibrary( true );
		$data = [];
		foreach ( $componentLibrary->getKnownComponents() as $component ) {
			$data[$component] = [ $component ];
		}
		return $data;
	}

	/**
	 * @param array $parserHookData
	 */
	private function doTestCanCreateHookFunctions( $parserHookData ) {
		$this->assertInternalType(
			'array',
			$parserHookData
		);
		$this->assertEquals(
			3,
			count( $parserHookData )
		);
		$this->assertInternalType(
			'string',
			$parserHookData[0]
		);
		$this->assertRegExp(
			'/' . ComponentFunctionFactory::PARSER_HOOK_PREFIX . '[a-z0-9_-]+/', $parserHookData[0]
		);
		$this->assertInternalType(
			'string',
			$parserHookData[1]
		);
		$this->assertContains(
			$parserHookData[1],
			[ ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION, ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION ]
		);
		$this->assertEquals(
			true,
			is_callable( $parserHookData[2] )
		);
	}
}
