<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\ApplicationFactory;
use BootstrapComponents\ComponentLibrary;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\ApplicationFactory
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  oetterer
 */
class ApplicationFactoryTest extends PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\ApplicationFactory',
			new ApplicationFactory()
		);
	}

	/**
	 * @param string $application
	 *
	 * @dataProvider applicationNameProvider
	 */
	public function testGetApplication( $application ) {
		$instance = new ApplicationFactory();
		$this->assertInstanceOf(
			'BootstrapComponents\\' . $application,
			call_user_func( [ $instance, 'get' . $application ] )
		);
	}

	public function testGetComponentFunctionFactory() {
		$instance = new ApplicationFactory();

		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$this->assertInstanceOf(
			'BootstrapComponents\\ComponentFunctionFactory',
			$instance->getComponentFunctionFactory( $parser, [] )
		);
	}

	public function testGetParserOutputHelper() {
		$instance = new ApplicationFactory();

		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$this->assertInstanceOf(
			'BootstrapComponents\\ParserOutputHelper',
			$instance->getParserOutputHelper( $parser )
		);
	}

	/**
	 * @param array  $arguments
	 * @param string $handlerType
	 *
	 * @dataProvider parserRequestProvider
	 */
	public function testGetNewParserRequest( $arguments, $handlerType ) {
		$instance = new ApplicationFactory();

		$this->assertInstanceOf(
			'BootstrapComponents\\ParserRequest',
			$instance->getNewParserRequest( $arguments, $handlerType )
		);
	}

	/**
	 * @param array  $arguments
	 * @param string $handlerType
	 *
	 * @dataProvider parserRequestFailureProvider
	 */
	public function testFailingGetNewParserRequest( $arguments, $handlerType ) {
		$instance = new ApplicationFactory();

		$this->setExpectedException( 'MWException' );

		$instance->getNewParserRequest( $arguments, $handlerType );
	}

	/**
	 * @return array[]
	 */
	public function applicationNameProvider() {
		return [
			'AttributeManager'  => [ 'AttributeManager' ],
			'ComponentLibrary'  => [ 'ComponentLibrary' ],
			'NestingController' => [ 'NestingController' ],
		];
	}

	/**
	 * @return array[]
	 */
	public function parserRequestProvider() {
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$frame = $this->getMockBuilder( 'PPFrame' )
			->disableOriginalConstructor()
			->getMock();
		return [
			'simpleTE' => [
				[ 'input', [], $parser, $frame ],
				ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION,
			],
			'simplePF' => [
				[ $parser, 'input', 'help' ],
				ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION,
			],
		];
	}

	/**
	 * @return array[]
	 */
	public function parserRequestFailureProvider() {
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$frame = $this->getMockBuilder( 'PPFrame' )
			->disableOriginalConstructor()
			->getMock();
		return [
			'wrongHandlerType PF instead of TE' => [
				[ 'input', [], $parser, $frame ],
				ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION,
			],
			'emptyPF'                           => [
				[],
				ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION,
			],
			'Parser Function no parser'         => [
				[ '1', '2', '3' ],
				ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION,
			],
			'Tag Extensions no parser'          => [
				[ '1', '2', '3' ],
				ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION,
			],
		];
	}
}
