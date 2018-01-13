<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\ComponentLibrary;
use BootstrapComponents\ParserOutputHelper;
use \MWException;
use \Parser;
use \PHPUnit_Framework_MockObject_MockObject;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\ParserOutputHelper
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer
 */
class ParserOutputHelperTest extends PHPUnit_Framework_TestCase {
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

	/**
	 * @param bool $expectError
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 */
	private function buildFullyEquippedParser( $expectError = true ) {
		$outputPropertyReturnString = 'rnd_string';
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		if ( $expectError ) {
			$parserOutput = $this->getMockBuilder( 'ParserOutput' )
				->disableOriginalConstructor()
				->getMock();
			$parserOutput->expects( $this->once() )
				->method( 'getProperty' )
				->with(
					$this->equalTo( 'defaultsort' )
				)
				->willReturn( $outputPropertyReturnString );
			$parserOutput->expects( $this->once() )
				->method( 'addCategory' )
				->with(
					$this->equalTo( 'Pages_with_bootstrap_component_errors' ),
					$this->equalTo( $outputPropertyReturnString )
				)
				->willReturn( $parserOutput );
			$parser->expects( $this->once() )
				->method( 'getOutput' )
				->willReturn( $parserOutput );
		}

		return $parser;
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\ParserOutputHelper',
			new ParserOutputHelper( $this->parser )
		);
	}

	public function testCanAddErrorTrackingCategory() {

		/** @noinspection PhpParamsInspection */
		$instance = new ParserOutputHelper(
			$this->buildFullyEquippedParser()
		);

		$instance->addErrorTrackingCategory();
	}

	/**
	 * This is so lame to test. Only reason to do this to up test coverage.
	 */
	public function testAddModules() {
		$parserOutput = $this->getMockBuilder( 'ParserOutput' )
			->disableOriginalConstructor()
			->getMock();
		$parserOutput->expects( $this->exactly( 4 ) )
			->method( 'addModules' )
			->will( $this->returnArgument( 0 ) );
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$parser->expects( $this->exactly( 4 ) )
			->method( 'getOutput' )
			->willReturn( $parserOutput );

		/** @noinspection PhpParamsInspection */
		$instance = new ParserOutputHelper( $parser );

		$instance->addModules( /** @scrutinizer ignore-type */ null );

		$instance->addModules( [] );

		/** @noinspection PhpParamsInspection */
		$instance->addModules( /** @scrutinizer ignore-type */ 'module0' );

		$instance->addModules( [ 'module1', 'module2' ] );
	}

	public function testCanAddTrackingCategory() {
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$parser->expects( $this->never() )
			->method( 'getOutput' )
			->willReturn( false );

		/** @noinspection PhpParamsInspection */
		$instance = new ParserOutputHelper( $parser );

		$instance->addTrackingCategory();
	}

	public function testGetNameOfActiveSkin() {
		$instance = new ParserOutputHelper( $this->parser );

		$this->assertEquals(
			'vector',
			$instance->getNameOfActiveSkin()
		);
	}

	public function testLoadBootstrapModules() {
		$parserOutput = $this->getMockBuilder( 'ParserOutput' )
			->disableOriginalConstructor()
			->getMock();
		$parserOutput->expects( $this->once() )
			->method( 'addModuleStyles' )
			->will( $this->returnArgument( 0 ) );
		$parserOutput->expects( $this->once() )
			->method( 'addModuleScripts' )
			->will( $this->returnArgument( 0 ) );
		$parserOutput->expects( $this->once() )
			->method( 'addModules' )
			->will( $this->returnArgument( 0 ) );
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$parser->expects( $this->exactly( 3 ) )
			->method( 'getOutput' )
			->willReturn( $parserOutput );

		/** @noinspection PhpParamsInspection */
		$instance = new ParserOutputHelper( $parser );

		$instance->loadBootstrapModules();
	}

	/**
	 * @param string $messageText
	 * @param string $renderedMessage
	 *
	 * @dataProvider errorMessageProvider
	 */
	public function testRenderErrorMessage( $messageText, $renderedMessage ) {
		/** @noinspection PhpParamsInspection */
		$instance = new ParserOutputHelper(
			$this->buildFullyEquippedParser( ( $renderedMessage != '~^$~' ) )
		);

		$this->assertRegExp(
			$renderedMessage,
			$instance->renderErrorMessage( $messageText )
		);
	}

	public function testVectorSkinInUse() {
		$instance = new ParserOutputHelper( $this->parser );
		$this->assertInternalType(
			'bool',
			$instance->vectorSkinInUse()
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
	 * @return array[]
	 */
	public function errorMessageProvider() {
		return [
			'null'       => [ null, '~^$~' ],
			'false'      => [ false, '~^$~' ],
			'none'       => [ '', '~^$~' ],
			'empty'      => [ '      ', '~^$~' ],
			'word'       => [ '__rndErrorMessageTextNotInMessageFiles', '~^<span class="error">[^_]+__rndErrorMessageTextNotInMessageFiles[^<]+</span>$~' ],
			'word space' => [ '  __rndErrorMessageTextNotInMessageFiles  ', '~^<span class="error">[^_]+__rndErrorMessageTextNotInMessageFiles[^<]+</span>$~' ],
		];
	}
}
