<?php

namespace Bootstrap\Components\Tests;

use BootstrapComponents\Setup as Setup;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\Setup
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  oetterer
 */
class SetupTest extends PHPUnit_Framework_TestCase {
	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\Setup',
			new Setup()
		);
	}

	/**
	 * @param array $configuration
	 * @param array $expectedRegisteredHooks
	 * @param array $expectedNotRegisteredHooks
	 *
	 * @dataProvider hookRegistryProvider
	 */
	public function testRegisterHooks( $configuration, $expectedRegisteredHooks, $expectedNotRegisteredHooks ) {
		$setup = new Setup();
		$setup->register( $configuration );
		$registeredHooks = $setup->getHooksToRegister( $configuration );

		foreach ( $expectedRegisteredHooks as $expectedHook ) {
			$this->doTestHookIsRegistered( $setup, $registeredHooks, $expectedHook );
		}

		foreach ( $expectedNotRegisteredHooks as $notExpectedHook ) {
			$this->doTestHookIsNotRegistered( $registeredHooks, $notExpectedHook );
		}
	}

	/**
	 * @param Setup  $setup
	 * @param array  $registeredHooks
	 * @param string $expectedHook
	 */
	private function doTestHookIsRegistered( Setup $setup, $registeredHooks, $expectedHook ) {
		$this->assertTrue(
			$setup->isRegistered( $expectedHook )
		);
		$this->assertArrayHasKey(
			$expectedHook,
			$registeredHooks
		);
		$this->assertTrue(
			is_callable( $registeredHooks[$expectedHook] )
		);
	}

	/**
	 * @param array  $registeredHooks
	 * @param string $notExpectedHook
	 */
	private function doTestHookIsNotRegistered( $registeredHooks, $notExpectedHook ) {
		$this->assertArrayNotHasKey(
			$notExpectedHook,
			$registeredHooks,
			'Expected hook "' . $notExpectedHook . '" to not be registered! '
		);
	}

	/**
	 * @return string[]
	 */
	public function hookRegistryProvider() {
		return [
			'onlydefault' => [
				[],
				[ 'ParserFirstCallInit', 'SetupAfterCache' ],
				[ 'GalleryGetModes', 'ImageBeforeProduceHTML' ],
			],
			'gallery activated' => [
				[ 'wgBootstrapComponentsEnableCarouselGalleryMode' => true ],
				[ 'GalleryGetModes', 'ParserFirstCallInit', 'SetupAfterCache' ],
				[ 'ImageBeforeProduceHTML' ],
			],
			'image replacement activated' => [
				[ 'wgBootstrapComponentsModalReplaceImageThumbnail' => true ],
				[ 'ImageBeforeProduceHTML', 'ParserFirstCallInit', 'SetupAfterCache' ],
				[ 'GalleryGetModes' ],
			],
			'both activated' => [
				[ 'wgBootstrapComponentsEnableCarouselGalleryMode' => true, 'wgBootstrapComponentsModalReplaceImageThumbnail' => true ],
				[ 'GalleryGetModes', 'ImageBeforeProduceHTML', 'ParserFirstCallInit', 'SetupAfterCache' ],
				[],
			],
		];
	}
}
