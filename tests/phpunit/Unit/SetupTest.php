<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\ComponentLibrary;
use BootstrapComponents\Setup as Setup;
use \Parser;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\Setup
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
class SetupTest extends PHPUnit_Framework_TestCase {
	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\Setup',
			new Setup()
		);
	}

	/**
	 * @throws \ConfigException cascading {@see \BootstrapComponents\Setup::onExtensionLoad}
	 * @throws \MWException
	 */
	public function testOnExtensionLoad() {
		$this->assertTrue(
			Setup::onExtensionLoad( [] )
		);
	}

	public function testCanCreateGalleryGetModes() {
		$setup = new Setup();

		$closure = $setup->createGalleryGetModes();

		$this->assertTrue(
			is_callable( $closure )
		);

		$galleryModes = [];
		$this->assertTrue(
			$closure( $galleryModes )
		);

		$this->assertArrayHasKey(
			'carousel', $galleryModes
		);

		$this->assertTrue(
			is_subclass_of( $galleryModes['carousel'], 'ImageGalleryBase' )
		);
	}

	public function testCreateImageBeforeProduceHTML() {
		$setup = new Setup();

		$nestingController = $this->getMockBuilder( 'BootstrapComponents\\NestingController' )
			->disableOriginalConstructor()
			->getMock();
		$myConfig = $this->getMockBuilder( 'Config' )
			->disableOriginalConstructor()
			->getMock();

		/** @noinspection PhpParamsInspection */
		$closure = $setup->createImageBeforeProduceHTML( $nestingController, $myConfig );

		$this->assertTrue(
			is_callable( $closure )
		);

		$linker = $title = $file = $frameParams = $handlerParams = $time = $res = false;

		$this->assertTrue(
			$closure( $linker, $title, $file, $frameParams, $handlerParams, $time, $res )
		);
	}

	/**
	 * @throws \ConfigException
	 */
	public function testCanCreateParserFirstCallInitCallback() {

		$nestingController = $this->getMockBuilder( 'BootstrapComponents\\NestingController' )
			->disableOriginalConstructor()
			->getMock();

		$setup = new Setup();
		$prefix = ComponentLibrary::PARSER_HOOK_PREFIX;

		/** @noinspection PhpParamsInspection */
		$closure = $setup->createParserFirstCallInitCallback(
			new ComponentLibrary( true ),
			$nestingController
		);

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
		$closure( $observerParser );
		# we have to call $closure with an observer (of type parser)
		# see to it, that functions setHook() and setFunctionHook() get called the right amount of times with the correct parameters
	}

	/**
	 * @param string $componentName
	 *
	 * @expectedException \ReflectionException
	 *
	 * @dataProvider CanCreateParserHookCallbackProvider
	 */
	public function testCanCreateParserHookCallbackFor( $componentName ) {

		$componentLibrary = $this->getMockBuilder( 'BootstrapComponents\\ComponentLibrary' )
			->disableOriginalConstructor()
			->getMock();
		$nestingController = $this->getMockBuilder( 'BootstrapComponents\\NestingController' )
			->disableOriginalConstructor()
			->getMock();
		$parserOutputHelper = $this->getMockBuilder( 'BootstrapComponents\\ParserOutputHelper' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new Setup();

		/** @noinspection PhpParamsInspection */
		$callback = $instance->createParserHookCallbackFor( $componentName, $componentLibrary, $nestingController, $parserOutputHelper );

		$this->assertTrue(
			is_callable( $callback )
		);

		$this->setExpectedException( 'ReflectionException' );
		$callback();
	}

	public function testCanCreateSetupAfterCache() {
		$setup = new Setup();

		$closure = $setup->createSetupAfterCache();

		$this->assertTrue(
			is_callable( $closure )
		);

		$this->assertTrue(
			$closure()
		);
	}

	/**
	 * @throws \ConfigException
	 * @throws \MWException
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
	 * @throws \MWException
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
	 * @throws \MWException
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

		$linker = $title = $file = $frameParams = $handlerParams = $time = $res = false;

		$this->assertTrue(
			$registeredHooks['ImageBeforeProduceHTML']( $linker, $title, $file, $frameParams, $handlerParams, $time, $res )
		);
	}

	public function testCanRegisterMyConfiguration() {

		$configFactory = $this->getMockBuilder( 'ConfigFactory' )
			->disableOriginalConstructor()
			->getMock();
		$configFactory->expects( $this->once() )
			->method( 'register' )
			->willReturn( true );

		$setup = new Setup();
		/** @noinspection PhpParamsInspection */
		$setup->registerMyConfiguration( $configFactory );
	}

	/**
	 * @param array $configuration
	 * @param array $expectedRegisteredHooks
	 * @param array $expectedNotRegisteredHooks
	 *
	 * @throws \ConfigException cascading {@see \Config::get}
	 * @throws \MWException
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
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	public function testCanInitializeApplications() {
		$setup = new Setup();
		$config = $this->getMockBuilder( 'Config' )
			->disableOriginalConstructor()
			->getMock();
		$config->expects( $this->once() )
			->method( 'get' )
			->willReturn( true );

		/** @noinspection PhpParamsInspection */
		list( $cl, $nc ) = $setup->initializeApplications( $config );

		$this->assertInstanceOf(
			'BootstrapComponents\\ComponentLibrary',
			$cl
		);
		$this->assertInstanceOf(
			'BootstrapComponents\\NestingController',
			$nc
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
	 * @throws \ConfigException
	 *
	 * @return array
	 */
	public function CanCreateParserHookCallbackProvider() {
		// this is lazy but efficient
		$componentLibrary = new ComponentLibrary( true );
		$data = [];
		foreach ( $componentLibrary->getKnownComponents() as $component ) {
			$data[$component] = [ $component ];
		}
		return $data;
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
				[ 'BootstrapComponentsModalReplaceImageTag' ],
				[ 'ImageBeforeProduceHTML', 'ParserFirstCallInit', 'SetupAfterCache' ],
				[ 'GalleryGetModes' ],
			],
			'both activated' => [
				[ 'BootstrapComponentsEnableCarouselGalleryMode', 'BootstrapComponentsModalReplaceImageTag' ],
				[ 'GalleryGetModes', 'ImageBeforeProduceHTML', 'ParserFirstCallInit', 'SetupAfterCache' ],
				[],
			],
		];
	}
}
