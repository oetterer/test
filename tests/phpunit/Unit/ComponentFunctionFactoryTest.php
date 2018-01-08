<?php

namespace Bootstrap\Components\Tests;

use BootstrapComponents\ComponentFunctionFactory;
use BootstrapComponents\ComponentLibrary;
use BootstrapComponents\Setup as Setup;
use \MWException;
use \Parser;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\ComponentFunctionFactory
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  oetterer
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

	/**
	 * @throws MWException
	 */
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
			15,
			count( $parserHookList )
		);
		foreach ( $parserHookList as $parserHookData ) {
			$this->doTestCanCreateHookFunctions( $parserHookData );
		}
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
