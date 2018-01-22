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
		// again
		$this->assertInstanceOf(
			'BootstrapComponents\\' . $application,
			call_user_func( [ $instance, 'get' . $application ] )
		);
		// and again
		$this->assertInstanceOf(
			'BootstrapComponents\\' . $application,
			call_user_func( [ $instance, 'get' . $application ] )
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
	 * @param array $arguments
	 * @param bool  $isParserFunction
	 *
	 * @dataProvider parserRequestProvider
	 */
	public function testGetNewParserRequest( $arguments, $isParserFunction ) {
		$instance = new ApplicationFactory();

		$this->assertInstanceOf(
			'BootstrapComponents\\ParserRequest',
			$instance->getNewParserRequest( $arguments, $isParserFunction )
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
	 * @param array $arguments
	 * @param bool  $isParserFunction
	 *
	 * @expectedException \MWException
	 * @dataProvider parserRequestFailureProvider
	 */
	public function testFailingGetNewParserRequest( $arguments, $isParserFunction ) {
		$instance = new ApplicationFactory();

		$this->setExpectedException( 'MWException' );

		$instance->getNewParserRequest( $arguments, $isParserFunction );
	}

	/**
	 * @throws \MWException
	 */
	public function testCanRegisterApplication() {
		$instance = new ApplicationFactory();
		$this->assertTrue(
			$instance->registerApplication( 'test', 'ReflectionClass' )
		);
	}

	/**
	 * @throws \MWException
	 */
	public function testCanNotRegisterApplicationOnInvalidName() {
		$instance = new ApplicationFactory();
		$this->assertTrue(
			!$instance->registerApplication( '', 'ReflectionClass' )
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
		$this->assertTrue(
			!$instance->resetLookup( 'hasBeenReset' )
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
