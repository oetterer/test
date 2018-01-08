<?php

namespace Bootstrap\Components\Tests;

use BootstrapComponents\Component\Accordion as Accordion;
use \MWException;

/**
 * @covers  \BootstrapComponents\Component\Accordion
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  oetterer
 */
class AccordionTest extends ComponentsTestBase {

	private $input = 'Accordion test text';

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\\BootstrapComponents\\Component\\Accordion',
			new Accordion(
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
		$instance = new Accordion(
			$this->getComponentLibrary(),
			$this->getParserOutputHelper(),
			$this->getNestingController()
		);

		$parserRequest = $this->buildParserRequest( $input, $arguments );

		/** @noinspection PhpParamsInspection */
		$generatedOutput = $instance->parseComponent( $parserRequest );
		$this->assertEquals(
			$expectedOutput,
			$generatedOutput
		);
	}

	/**
	 * @return array
	 */
	public function placeMeArgumentsProvider() {
		return [
			'simple'        => [
				$this->input,
				[],
				'<div class="panel-group" id="bsc_accordion_NULL">' . $this->input . '</div>',
			],
			'add_css_class' => [
				$this->input,
				[ 'class' => 'test' ],
				'<div class="panel-group test" id="bsc_accordion_NULL">' . $this->input . '</div>',
			],
		];
	}
}
