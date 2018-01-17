<?php

namespace BootstrapComponents\Tests\Unit\Component;

use BootstrapComponents\Component\Panel;
use BootstrapComponents\Tests\Unit\ComponentsTestBase;
use \MWException;

/**
 * @covers  \BootstrapComponents\Component\Panel
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
class PanelTest extends ComponentsTestBase {

	private $input = 'Panel test text';

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\Component\\Panel',
			new Panel(
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
		$instance = new Panel(
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
			'simple'         => [
				$this->input,
				[],
				'<div class="panel panel-default"><div id="bsc_panel_NULL"><div class="panel-body">' . $this->input . '</div></div></div>',
			],
			'text missing'   => [
				'',
				[ 'heading' => 'watch this', 'footer' => 'watch what?' ],
				'<div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title" style="margin-top:0;padding-top:0;">watch this</h4></div><div id="bsc_panel_NULL"><div class="panel-body"></div><div class="panel-footer">watch what?</div></div></div>',
			],
			'all attributes' => [
				$this->input,
				[
					'class'       => 'dummy nice', 'style' => 'float:right;background-color:green', 'id' => 'badgers_bowler', 'active' => true,
					'color'       => 'info',
					'collapsible' => true, 'heading' => 'HEADING TEXT', 'footer' => 'FOOTER TEXT',
				],
				'<div class="panel panel-info dummy nice" style="float:right;background-color:green"><div class="panel-heading" data-toggle="collapse" href="#badgers_bowler"><h4 class="panel-title" style="margin-top:0;padding-top:0;">HEADING TEXT</h4></div><div id="badgers_bowler" class="panel-collapse collapse fade in"><div class="panel-body">' . $this->input . '</div><div class="panel-footer">FOOTER TEXT</div></div></div>',
			],
		];
	}
}
