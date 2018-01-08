<?php

namespace Bootstrap\Components\Tests;

use BootstrapComponents\Component\Badge;
use \MWException;

/**
 * @covers  \BootstrapComponents\Component\Badge
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  oetterer
 */
class BadgeTest extends ComponentsTestBase {

	private $input = 'Badge test text';

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\\BootstrapComponents\\Component\\Badge',
			new Badge(
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
		$instance = new Badge(
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
				'<span class="badge" id="bsc_badge_NULL">' . $this->input . '</span>',
			],
			'empty'           => [
				'',
				[],
				'bootstrap-components-badge-content-missing',
			],
			'manual id'       => [
				$this->input,
				[ 'id' => 'book' ],
				'<span class="badge" id="book">' . $this->input . '</span>',
			],
			'style and class' => [
				$this->input,
				[ 'class' => 'dummy nice', 'style' => 'float:right;background-color:#80266e' ],
				'<span class="badge dummy nice" style="float:right;background-color:#80266e" id="bsc_badge_NULL">' . $this->input . '</span>',
			],
		];
	}
}
