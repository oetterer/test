<?php

namespace Bootstrap\Components\Tests;

use BootstrapComponents\Component\Popover;
use \MWException;

/**
 * @covers  \BootstrapComponents\Component\Popover
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  oetterer
 */
class PopoverTest extends ComponentsTestBase {

	private $input = 'Popover test text';

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\\BootstrapComponents\\Component\\Popover',
			new Popover(
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
		$instance = new Popover(
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
			'simple'          => [
				$this->input,
				[ 'heading' => 'heading', 'text' => 'BUTTON' ],
				'<button class="btn btn-info" id="bsc_popover_NULL" data-toggle="popover" title="heading" data-content="' . $this->input . '" type="submit">BUTTON</button>',
			],
			'heading missing' => [
				$this->input,
				[ 'heading' => '', 'text' => 'BUTTON' ],
				'bootstrap-components-popover-heading-missing',
			],
			'text missing'    => [
				$this->input,
				[ 'heading' => 'heading', 'text' => '' ],
				'bootstrap-components-popover-text-missing',
			],
			'all attributes'  => [
				$this->input,
				[
					'heading'   => 'heading', 'text' => 'BUTTON', 'class' => 'dummy nice', 'style' => 'float:right;background-color:green',
					'placement' => 'right', 'trigger' => 'hover', 'id' => 'cudgel',
				],
				'<button class="btn btn-info dummy nice" style="float:right;background-color:green" id="cudgel" data-toggle="popover" title="heading" data-content="' . $this->input . '" data-placement="right" data-trigger="hover" type="submit">BUTTON</button>',
			],
		];
	}
}
