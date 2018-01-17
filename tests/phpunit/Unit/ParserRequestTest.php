<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\ComponentLibrary;
use BootstrapComponents\ParserRequest;
use \MWException;
use \PHPUnit_Framework_TestCase;
use \Parser;
use \PPFrame;

/**
 * @covers  \BootstrapComponents\ParserRequest
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
class ParserRequestTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var PPFrame
	 */
	private $frame;

	/**
	 * @var Parser
	 */
	private $parser;

	public function setUp() {
		parent::setUp();
		$this->frame = $this->getMockBuilder( 'PPFrame' )
			->disableOriginalConstructor()
			->getMock();
		$this->parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * @param array  $arguments
	 * @param string $handlerType
	 *
	 * @dataProvider constructionProvider
	 */
	public function testCanConstruct( $arguments, $handlerType ) {

		$this->assertInstanceOf(
			'BootstrapComponents\\ParserRequest',
			new ParserRequest( $arguments, $handlerType )
		);
	}

	/**
	 * @param array  $arguments
	 * @param string $handlerType
	 *
	 * @expectedException \MWException
	 *
	 * @dataProvider constructionFailsProvider
	 */
	public function testCanNotConstruct( $arguments, $handlerType ) {

		$this->setExpectedException( 'MWException' );

		$this->assertInstanceOf(
			'BootstrapComponents\\ParserRequest',
			new ParserRequest( $arguments, $handlerType )
		);
	}

	/**
	 * @param array  $arguments
	 * @param string $handlerType
	 * @param string $expectedInput
	 * @param array  $expectedAttributes
	 *
	 * @dataProvider constructionProvider
	 */
	public function testGetAttributesAndInput( $arguments, $handlerType, $expectedInput, $expectedAttributes ) {
		$instance = new ParserRequest( $arguments, $handlerType );

		$this->assertEquals(
			$expectedInput,
			$instance->getInput()
		);

		$this->assertEquals(
			$expectedAttributes,
			$instance->getAttributes()
		);

		$this->assertInstanceOf(
			'Parser',
			$instance->getParser()
		);

		if ( $handlerType == ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION ) {
			$this->assertInstanceOf(
				'PPFrame',
				$instance->getFrame()
			);
		} else {
			$this->assertInternalType(
				'null',
				$instance->getFrame()
			);
		}
	}

	/**
	 * @return array[]
	 */
	public function constructionProvider() {
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$frame = $this->getMockBuilder( 'PPFrame' )
			->disableOriginalConstructor()
			->getMock();
		$inputText = 'input';
		return [
			'pf'          => [
				[ $parser, $inputText ],
				ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION,
				$inputText,
				[]
			],
			'te'          => [
				[ $inputText, [], $parser, $frame ],
				ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION,
				$inputText,
				[]
			],
			'pf many'     => [
				[ $parser, $inputText, 'attr1=1', 'attr2=2', 'attr3=3', 'single', ],
				ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION,
				$inputText,
				[ 'attr1' => '1', 'attr2' => '2', 'attr3' => '3', 'single' => true, ],
			],
			'te many'     => [
				[ $inputText, [ 'attr1' => '1', 'attr2' => '2', 'attr3' => '3', 'single' => true, ], $parser, $frame ],
				ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION,
				$inputText,
				[ 'attr1' => '1', 'attr2' => '2', 'attr3' => '3', 'single' => true, ],
			],
			'pf no input' => [
				[ $parser, '', '', 'attr1=1', 'attr2=2', 'attr3=3', ],
				ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION,
				'',
				[ 'attr1' => '1', 'attr2' => '2', 'attr3' => '3', ],
			],
			'te no input' => [
				[ '', [ 'attr1' => '1', 'attr2' => '2', 'attr3' => '3', ], $parser, $frame ],
				ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION,
				'',
				[ 'attr1' => '1', 'attr2' => '2', 'attr3' => '3', ],
			],
		];
	}

	/**
	 * @return array[]
	 */
	public function constructionFailsProvider() {
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$frame = $this->getMockBuilder( 'PPFrame' )
			->disableOriginalConstructor()
			->getMock();
		return [
			'pf'  => [ [ null, 'input' ], ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION ],
			'pf0' => [ [ null ], ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION ],
			'pf1' => [ [ $parser, '', false ], ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION ],
			'te'  => [ [ 'input', [], null, $frame ], ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION ],
			'te1' => [ [ 'input', [ false ], $parser ], ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION ],
			'te2' => [ [ 'input', [ 13 ], $parser ], ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION ],
			'te3' => [ [ 'input', [], $parser ], ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION ],
			'uk1' => [ [ $parser, 'input', [] ], 'FooBar' ],
			'uk2' => [ [ 'input', [], $parser, $frame ], 'FooBar' ],
		];
	}
}
