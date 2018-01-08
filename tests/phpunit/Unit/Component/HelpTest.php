<?php

namespace BootstrapComponents\Tests\Unit\Component;

use BootstrapComponents\Component\Help;
use BootstrapComponents\Tests\Unit\ComponentsTestBase;
use \MWException;

/**
 * @covers  \BootstrapComponents\Component\Help
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  oetterer
 */
class HelpTest extends ComponentsTestBase {

	private $input = 'Help test text';

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\Component\\Help',
			new Help(
				$this->getComponentLibrary(),
				$this->getParserOutputHelper(),
				$this->getNestingController()
			)
		);
	}

	/**
	 * @throws MWException
	 */
	public function testCanRender() {
		$instance = new Help(
			$this->getComponentLibrary(),
			$this->getParserOutputHelper(),
			$this->getNestingController()
		);

		$parserRequest = $this->buildParserRequest( '', [] );

		/** @noinspection PhpParamsInspection */
		$this->assertInternalType(
			'string',
			$instance->parseComponent( $parserRequest )
		);
	}
}
