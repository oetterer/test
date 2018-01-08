<?php

namespace Bootstrap\Components\Tests;

use BootstrapComponents\Component\Label;
use \MWException;

/**
 * @covers  \BootstrapComponents\Component\Label
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  oetterer
 */
class LabelTest extends ComponentsTestBase {

	private $input = 'Label test text';

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\\BootstrapComponents\\Component\\Label',
			new Label(
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
		$instance = new Label(
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
				[],
				'<span class="label label-default" id="bsc_label_NULL">' . $this->input . '</span>',
			],
			'empty'           => [
				'',
				[],
				'bootstrap-components-label-content-missing',
			],
			'style and class' => [
				$this->input,
				[ 'class' => 'dummy nice', 'style' => 'float:right;background-color:#80266e' ],
				'<span class="label label-default dummy nice" style="float:right;background-color:#80266e" id="bsc_label_NULL">' . $this->input . '</span>',
			],
			'manual id'       => [
				$this->input,
				[ 'id' => 'dinosaur', 'color' => 'warning' ],
				'<span class="label label-warning" id="dinosaur">' . $this->input . '</span>',
			],
		];
	}
}
