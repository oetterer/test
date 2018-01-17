<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\ApplicationFactory;
use BootstrapComponents\ComponentLibrary;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\ApplicationFactory
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
	public function testGetApplicationAndReset( $application ) {
		$instance = new ApplicationFactory();
		$this->assertInstanceOf(
			'BootstrapComponents\\' . $application,
			call_user_func( [ $instance, 'get' . $application ] )
		);
		$this->assertTrue(
			$instance->resetLookup( $application )
		);
	}

	public function testGetModalBuilder() {
		$instance = new ApplicationFactory();

		$this->assertInstanceOf(
			'BootstrapComponents\\ModalBuilder',
			$instance->getModalBuilder( '', '', '' )
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
	 * @throws \MWException
	 */
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
	 * @expectedException \MWException
	 *
	 * @dataProvider parserRequestFailureProvider
	 */
	public function testFailingGetNewParserRequest( $arguments, $handlerType ) {
		$instance = new ApplicationFactory();

		$this->setExpectedException( 'MWException' );

		$instance->getNewParserRequest( $arguments, $handlerType );
	}

	public function testCanRegisterApplication() {
		$instance = new ApplicationFactory();
		$this->assertTrue(
			$instance->registerApplication( 'test', 'ReflectionClass' )
		);
	}

	public function testCanNotRegisterApplicationOnInvalidName() {
		$instance = new ApplicationFactory();
		$this->assertTrue(
			!$instance->registerApplication( '', 'ReflectionClass' )
		);
		$this->assertTrue(
			!$instance->registerApplication( false, 'ReflectionClass' )
		);
		$this->assertTrue(
			!$instance->registerApplication( '   ', 'ReflectionClass' )
		);
	}

	/**
	 * @expectedException \MWException
	 */
	public function testCanNotRegisterApplicationOnInvalidClass() {
		$instance = new ApplicationFactory();
		$this->setExpectedException( 'MWException' );
		$instance->registerApplication( 'test', 'FooBar' );
	}

	public function testCanResetLookup() {
		$instance = new ApplicationFactory();
		$this->assertTrue(
			$instance->resetLookup()
		);
	}

	/**
	 * @return array[]
	 */
	public function applicationNameProvider() {
		return [
			'AttributeManager'         => [ 'AttributeManager' ],
			'ComponentLibrary'         => [ 'ComponentLibrary' ],
			'NestingController'        => [ 'NestingController' ],
			'ParserOutputHelper'       => [ 'ParserOutputHelper' ],
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
				false,
			],
			'simplePF' => [
				[ $parser, 'input', 'class=test' ],
				true,
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
				true,
			],
			'emptyPF'                           => [
				[],
				true,
			],
			'Parser Function no parser'         => [
				[ '1', '2', '3' ],
				true,
			],
			'Tag Extensions no parser'          => [
				[ '1', '2', '3', '4' ],
				false,
			],
			'Tag Extensions wrong #of args'     => [
				[ '1', '2', $parser ],
				false,
			],
		];
	}
}
