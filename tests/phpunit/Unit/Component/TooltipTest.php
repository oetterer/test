<?php

namespace BootstrapComponents\Tests\Unit\Component;

use BootstrapComponents\Component\Tooltip;
use BootstrapComponents\Tests\Unit\ComponentsTestBase;
use \MWException;

/**
 * @covers  \BootstrapComponents\Component\Tooltip
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  oetterer
 */
class TooltipTest extends ComponentsTestBase {

	private $input = 'Tooltip test text';

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\\BootstrapComponents\\Component\\Tooltip',
			new Tooltip(
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
		$instance = new Tooltip(
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
			'simple'              => [
				$this->input,
				[ 'text' => 'simple' ],
				'<span id="bsc_tooltip_NULL" data-toggle="tooltip" title="simple">' . $this->input . '</span>',
			],
			'empty'               => [
				'',
				[],
				'bootstrap-components-tooltip-content-missing',
			],
			'text missing'        => [
				$this->input,
				[],
				'bootstrap-components-tooltip-text-missing',
			],
			'id, style and class' => [
				$this->input,
				[ 'text' => 'simple', 'class' => 'dummy nice', 'style' => 'float:right;background-color:#80266e', 'id' => 'vera' ],
				'<span class="dummy nice" style="float:right;background-color:#80266e" id="vera" data-toggle="tooltip" title="simple">' . $this->input . '</span>',
			],
		];
	}
}
