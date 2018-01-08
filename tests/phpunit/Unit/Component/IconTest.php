<?php

namespace Bootstrap\Components\Tests;

use BootstrapComponents\Component\Icon;
use \MWException;

/**
 * @covers  \BootstrapComponents\Component\Icon
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  oetterer
 */
class IconTest extends ComponentsTestBase {

	private $input = 'icon';

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\\BootstrapComponents\\Component\\Icon',
			new Icon(
				$this->getComponentLibrary(),
				$this->getParserOutputHelper(),
				$this->getNestingController()
			)
		);
	}

	/**
	 * @param string $input
	 * @param array  $arguments
	 * @param string $expectedOutput
	 *
	 * @dataProvider placeMeArgumentsProvider
	 * @throws MWException
	 */
	public function testCanRender( $input, $arguments, $expectedOutput ) {
		$instance = new Icon(
			$this->getComponentLibrary(),
			$this->getParserOutputHelper(),
			$this->getNestingController()
		);

		$parserRequest = $this->buildParserRequest( $input, $arguments );

		/** @noinspection PhpParamsInspection */
		$generatedOutput = $instance->parseComponent( $parserRequest );

		$this->assertEquals( $expectedOutput, $generatedOutput );
	}

	/**
	 * @return array
	 */
	public function placeMeArgumentsProvider() {
		return [
			'simple' => [
				$this->input,
				[],
				'<span class="glyphicon glyphicon-' . $this->input . '"></span>',
			],
			'empty'  => [
				'',
				[],
				'bootstrap-components-glyph-icon-name-missing',
			],
		];
	}
}
