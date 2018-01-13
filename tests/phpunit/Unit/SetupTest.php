<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\ComponentFunctionFactory;
use BootstrapComponents\Setup as Setup;
use \Parser;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\Setup
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer
 */
class SetupTest extends PHPUnit_Framework_TestCase {
	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\Setup',
			new Setup()
		);
	}

	/**
	 * @throws \ConfigException cascading {@see \BootstrapComponents\Setup::onExtensionLoad}
	 */
	public function testOnExtensionLoad() {
		$this->assertTrue(
			Setup::onExtensionLoad( [] )
		);
	}

	/**
	 * @param array $configuration
	 * @param array $expectedRegisteredHooks
	 * @param array $expectedNotRegisteredHooks
	 *
	 * @throws \ConfigException cascading {@see \Config::get}
	 *
	 * @dataProvider hookRegistryProvider
	 */
	public function testRegisterHooks( $configuration, $expectedRegisteredHooks, $expectedNotRegisteredHooks ) {

		$myConfig = $this->getMockBuilder( 'Config' )
			->disableOriginalConstructor()
			->getMock();
		$returnMap = [];
		foreach ( $configuration as $setting ) {
			$returnMap[] = [ $setting, true ];
		}
		$myConfig->expects( $this->any() )
			->method( 'has' )
			->will( $this->returnValueMap( $returnMap ) );
		$myConfig->expects( $this->any() )
			->method( 'get' )
			->will( $this->returnValueMap( $returnMap ) );

		$setup = new Setup();
		/** @noinspection PhpParamsInspection */
		$setup->registerHooks( $myConfig );
		/** @noinspection PhpParamsInspection */
		$registeredHooks = $setup->getHooksToRegister( $myConfig );

		foreach ( $expectedRegisteredHooks as $expectedHook ) {
			$this->doTestHookIsRegistered( $setup, $registeredHooks, $expectedHook );
		}

		foreach ( $expectedNotRegisteredHooks as $notExpectedHook ) {
			$this->doTestHookIsNotRegistered( $registeredHooks, $notExpectedHook );
		}
	}

	public function testCanCreateParserFirstCallInitCallback() {

		$setup = new Setup();
		$prefix = ComponentFunctionFactory::PARSER_HOOK_PREFIX;

		$callBackForParserFirstCallInitHook = $setup->createParserFirstCallInitCallback();

		$observerParser = $this->getMockBuilder(Parser::class )
			->disableOriginalConstructor()
			->setMethods( [ 'setFunctionHook', 'setHook' ] )
			->getMock();
		$observerParser->expects( $this->exactly( 6 ) )
			->method( 'setFunctionHook' )
			->withConsecutive(
				[ $this->equalTo( $prefix . 'badge' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'button' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'carousel' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'icon' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'label' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'tooltip' ), $this->callback( 'is_callable' ) ]
			);
		$observerParser->expects( $this->exactly( 8 ) )
			->method( 'setHook' )
			->withConsecutive(
				[ $this->equalTo( $prefix . 'accordion' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'alert' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'collapse' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'jumbotron' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'modal' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'panel' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'popover' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'well' ), $this->callback( 'is_callable' ) ]
			);
		$callBackForParserFirstCallInitHook( $observerParser );
		# we have to call $callBackForParserFirstCallInitHook with an observer (of type parser)
		# see to it, that functions setHook() and setFunctionHook() get called the right amount of times with the correct parameters
	}

	/**
	 * @throws \ConfigException
	 */
	public function testHookSetupAfterCache() {

		$myConfig = $this->getMockBuilder( 'Config' )
			->disableOriginalConstructor()
			->getMock();

		$setup = new Setup();
		/** @noinspection PhpParamsInspection */
		$setup->registerHooks( $myConfig );
		/** @noinspection PhpParamsInspection */
		$registeredHooks = $setup->getHooksToRegister( $myConfig );

		$this->assertArrayHasKey(
			'SetupAfterCache',
			$registeredHooks
		);

		$this->assertTrue(
			is_callable( $registeredHooks['SetupAfterCache'] )
		);

		$this->assertTrue(
			$registeredHooks['SetupAfterCache']()
		);
	}

	/**
	 * @throws \ConfigException
	 */
	public function testHookGalleryGetModes() {

		$myConfig = $this->getMockBuilder( 'Config' )
			->disableOriginalConstructor()
			->getMock();
		$myConfig->expects( $this->any() )
			->method( 'has' )
			->willReturn( true );
		$myConfig->expects( $this->any() )
			->method( 'get' )
			->willReturn( true );

		$setup = new Setup();
		/** @noinspection PhpParamsInspection */
		$setup->registerHooks( $myConfig );
		/** @noinspection PhpParamsInspection */
		$registeredHooks = $setup->getHooksToRegister( $myConfig );

		$this->assertArrayHasKey(
			'GalleryGetModes',
			$registeredHooks
		);

		$this->assertTrue(
			is_callable( $registeredHooks['GalleryGetModes'] )
		);

		$galleryModes = [];
		$this->assertTrue(
			$registeredHooks['GalleryGetModes']( $galleryModes )
		);

		$this->assertArrayHasKey(
			'carousel', $galleryModes
		);

		$this->assertTrue(
			is_subclass_of( $galleryModes['carousel'], 'ImageGalleryBase' )
		);
	}

	/**
	 * @throws \ConfigException
	 */
	public function testHookImageBeforeProduceHTML() {

		$myConfig = $this->getMockBuilder( 'Config' )
			->disableOriginalConstructor()
			->getMock();
		$myConfig->expects( $this->any() )
			->method( 'has' )
			->willReturn( true );
		$myConfig->expects( $this->any() )
			->method( 'get' )
			->willReturn( true );

		$setup = new Setup();
		/** @noinspection PhpParamsInspection */
		$setup->registerHooks( $myConfig );
		/** @noinspection PhpParamsInspection */
		$registeredHooks = $setup->getHooksToRegister( $myConfig );

		$this->assertArrayHasKey(
			'ImageBeforeProduceHTML',
			$registeredHooks
		);

		$this->assertEquals(
			1,
			count( $registeredHooks['ImageBeforeProduceHTML'] )
		);

		$this->assertTrue(
			is_callable( $registeredHooks['ImageBeforeProduceHTML'] )
		);
	}

	public function testCanRegisterMyConfiguration() {

		$configFactory = $this->getMockBuilder( 'ConfigFactory' )
			->disableOriginalConstructor()
			->getMock();
		$configFactory->expects( $this->once() )
			->method( 'register' )
			->will( $this->returnArgument( true ) );

		$setup = new Setup();
		/** @noinspection PhpParamsInspection */
		$setup->registerMyConfiguration( $configFactory );
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
				[ 'BootstrapComponentsEnableCarouselGalleryMode' ],
				[ 'GalleryGetModes', 'ParserFirstCallInit', 'SetupAfterCache' ],
				[ 'ImageBeforeProduceHTML' ],
			],
			'image replacement activated' => [
				[ 'BootstrapComponentsModalReplaceImageThumbnail' ],
				[ 'ImageBeforeProduceHTML', 'ParserFirstCallInit', 'SetupAfterCache' ],
				[ 'GalleryGetModes' ],
			],
			'both activated' => [
				[ 'BootstrapComponentsEnableCarouselGalleryMode', 'BootstrapComponentsModalReplaceImageThumbnail' ],
				[ 'GalleryGetModes', 'ImageBeforeProduceHTML', 'ParserFirstCallInit', 'SetupAfterCache' ],
				[],
			],
		];
	}
}
