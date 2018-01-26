<?php

namespace BootstrapComponents\Tests\Unit\Component;

use BootstrapComponents\Component\Modal;
use BootstrapComponents\Tests\Unit\ComponentsTestBase;
use \MWException;

/**
 * @covers  \BootstrapComponents\Component\Modal
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
class ModalTest extends ComponentsTestBase {

	private $input = 'Modal test text';

	/**
	 * @throws \MWException
	 */
	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\Component\\Modal',
			new Modal(
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
		$instance = new Modal(
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
				[ 'text' => 'BUTTON' ],
				'<button type="button" class="modal-trigger btn btn-default" data-toggle="modal" data-target="#bsc_modal_NULL">BUTTON</button><div class="modal fade" role="dialog" id="bsc_modal_NULL" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
<div class="modal-body">' . $this->input . '</div>
<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>
</div></div></div>',
			],
			'text missing'        => [
				$this->input,
				[ 'text' => '' ],
				'bootstrap-components-modal-text-missing',
			],
			'image, size invalid' => [
				$this->input,
				[
					'text' => 'before<a href="/File:Serenity.png" class="image"><img alt="Serenity" src="/images/a/aa/Serenity.png" width="160" height="42"></a>after',
					'size' => 'none',
				],
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_modal_NULL">before<img alt="Serenity" src="/images/a/aa/Serenity.png" width="160" height="42">after</span><div class="modal fade" role="dialog" id="bsc_modal_NULL" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>
<div class="modal-body">' . $this->input . '</div>
<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>
</div></div></div>',
			],
			'all attributes'      => [
				$this->input,
				[
					'text'    => '<a href="/File:Serenity.png" class="image"><img alt="Serenity" src="/images/a/aa/Serenity.png" width="160" height="42"></a>',
					'id'      => 'firefly0', 'size' => 'lg', 'class' => 'shiny', 'style' => 'float:right;background-color:black',
					'heading' => 'You can\'t take the sky from me!',
				],
				'<span class="modal-trigger" data-toggle="modal" data-target="#firefly0"><img alt="Serenity" src="/images/a/aa/Serenity.png" width="160" height="42"></span><div class="modal fade shiny" style="float:right;background-color:black" role="dialog" id="firefly0" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><span class="modal-title">You can\'t take the sky from me!</span></div>
<div class="modal-body">' . $this->input . '</div>
<div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>
</div></div></div>',
			],
		];
	}
}
