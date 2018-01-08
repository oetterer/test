<?php

namespace BootstrapComponents\Tests\Unit\Component;

use BootstrapComponents\Component\Button;
use BootstrapComponents\Tests\Unit\ComponentsTestBase;
use \MWException;

/**
 * @covers  \BootstrapComponents\Component\Button
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  oetterer
 */
class ButtonTest extends ComponentsTestBase {

	private $input = 'Button test text';

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\Component\\Button',
			new Button(
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
		$instance = new Button(
			$this->getComponentLibrary(),
			$this->getParserOutputHelper(),
			$this->getNestingController()
		);

		$parserRequest = $this->buildParserRequest( $input, $arguments );

		/** @noinspection PhpParamsInspection */
		$generatedOutput = $instance->parseComponent( $parserRequest );
		if ( is_array( $generatedOutput ) ) {
			$generatedOutput = $generatedOutput[0];
		}

		$this->assertEquals( $expectedOutput, $generatedOutput );
	}

	/**
	 * @return array
	 */
	public function placeMeArgumentsProvider() {
		return [
			'simple'             => [
				$this->input,
				[],
				'<a class="btn btn-default" role="button" id="bsc_button_NULL" href="/' . str_replace( ' ', '_', $this->input ) . '">' . $this->input . '</a>',
			],
			'empty'              => [
				'',
				[],
				'bootstrap-components-button-target-missing',
			],
			'color, text and id' => [
				$this->input,
				[ 'color' => 'danger', 'text' => 'BUTTON', 'id' => 'red' ],
				'<a class="btn btn-danger" role="button" id="red" href="/' . str_replace( ' ', '_', $this->input ) . '">BUTTON</a>',
			],
		];
	}
}
