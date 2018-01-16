<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\ImageModal;
use Cdb\Exception;
use \ConfigException;
use \Linker;
use \MediaWiki\MediaWikiServices;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\ImageModal
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer
 */
class ImageModalTest extends PHPUnit_Framework_TestCase {

	const NUM_OF_RND_COMPARE_TESTS = 25;

	public function setUp() {
		parent::setUp();
		set_time_limit( 300 );
	}

	public function testCanConstruct() {

		$dummyLinker = $this->getMockBuilder( 'DummyLinker' )
			->disableOriginalConstructor()
			->getMock();

		$title = $this->getMockBuilder( 'Title' )
			->disableOriginalConstructor()
			->getMock();

		$localFile = $this->getMockBuilder( 'LocalFile' )
			->disableOriginalConstructor()
			->getMock();

		$file = $this->getMockBuilder( 'File' )
			->disableOriginalConstructor()
			->getMock();

		/** @noinspection PhpParamsInspection */
		$this->assertInstanceOf(
			'BootstrapComponents\\ImageModal',
			new ImageModal(
				$dummyLinker,
				$title,
				$localFile
			)
		);
		/** @noinspection PhpParamsInspection */
		$this->assertInstanceOf(
			'BootstrapComponents\\ImageModal',
			new ImageModal(
				$dummyLinker,
				$title,
				$file
			)
		);
	}

	/**
	 * @throws \MWException
	 * @throws \ConfigException
	 */
	public function testCanParseOnFileInvalid() {
		$dummyLinker = $this->getMockBuilder( 'DummyLinker' )
			->disableOriginalConstructor()
			->getMock();

		$title = $this->getMockBuilder( 'Title' )
			->disableOriginalConstructor()
			->getMock();

		$nestingController = $this->getMockBuilder( 'BootstrapComponents\\NestingController' )
			->disableOriginalConstructor()
			->getMock();

		$parserOutputHelper = $this->getMockBuilder( 'BootstrapComponents\\ParserOutputHelper' )
			->disableOriginalConstructor()
			->getMock();

		/** @noinspection PhpParamsInspection */
		$instance = new ImageModal( $dummyLinker, $title, /** @scrutinizer ignore-type */ false, $nestingController, $parserOutputHelper );
		$time = false;
		$res = '';

		$resultOfParseCall = $instance->parse( $fp, $hp, $time, $res );

		$this->assertTrue(
			$resultOfParseCall
		);
	}

	/**
	 * @throws \MWException
	 * @throws \ConfigException
	 */
	public function testCanParseOnFileNonExistent() {
		$dummyLinker = $this->getMockBuilder( 'DummyLinker' )
			->disableOriginalConstructor()
			->getMock();

		$title = $this->getMockBuilder( 'Title' )
			->disableOriginalConstructor()
			->getMock();

		$file = $this->getMockBuilder( 'LocalFile' )
			->disableOriginalConstructor()
			->getMock();
		$file->expects( $this->any() )
			->method( 'allowInlineDisplay' )
			->willReturn( true );
		$file->expects( $this->any() )
			->method( 'exists' )
			->willReturn( false );

		$nestingController = $this->getMockBuilder( 'BootstrapComponents\\NestingController' )
			->disableOriginalConstructor()
			->getMock();

		$parserOutputHelper = $this->getMockBuilder( 'BootstrapComponents\\ParserOutputHelper' )
			->disableOriginalConstructor()
			->getMock();

		/** @noinspection PhpParamsInspection */
		$instance = new ImageModal( $dummyLinker, $title, $file, $nestingController, $parserOutputHelper );
		$time = false;
		$res = '';

		$resultOfParseCall = $instance->parse( $fp, $hp, $time, $res );

		$this->assertTrue(
			$resultOfParseCall
		);
	}

	/**
	 * @throws \MWException
	 * @throws \ConfigException
	 */
	public function testCanParseOnFileNoAllowInlineParse() {
		$dummyLinker = $this->getMockBuilder( 'DummyLinker' )
			->disableOriginalConstructor()
			->getMock();

		$title = $this->getMockBuilder( 'Title' )
			->disableOriginalConstructor()
			->getMock();

		$file = $this->getMockBuilder( 'LocalFile' )
			->disableOriginalConstructor()
			->getMock();
		$file->expects( $this->any() )
			->method( 'allowInlineDisplay' )
			->willReturn( false );
		$file->expects( $this->any() )
			->method( 'exists' )
			->willReturn( true );

		$nestingController = $this->getMockBuilder( 'BootstrapComponents\\NestingController' )
			->disableOriginalConstructor()
			->getMock();

		$parserOutputHelper = $this->getMockBuilder( 'BootstrapComponents\\ParserOutputHelper' )
			->disableOriginalConstructor()
			->getMock();

		/** @noinspection PhpParamsInspection */
		$instance = new ImageModal( $dummyLinker, $title, $file, $nestingController, $parserOutputHelper );
		$time = false;
		$res = '';

		$resultOfParseCall = $instance->parse( $fp, $hp, $time, $res );

		$this->assertTrue(
			$resultOfParseCall
		);
	}

	/**
	 * @throws \MWException
	 * @throws \ConfigException
	 */
	public function testCanParseOnOnInvalidManualThumb() {
		$dummyLinker = $this->getMockBuilder( 'DummyLinker' )
			->disableOriginalConstructor()
			->getMock();

		$title = $this->getMockBuilder( 'Title' )
			->disableOriginalConstructor()
			->getMock();

		$file = $this->getMockBuilder( 'LocalFile' )
			->disableOriginalConstructor()
			->getMock();
		$file->expects( $this->any() )
			->method( 'allowInlineDisplay' )
			->willReturn( true );
		$file->expects( $this->any() )
			->method( 'exists' )
			->willReturn( true );

		$nestingController = $this->getMockBuilder( 'BootstrapComponents\\NestingController' )
			->disableOriginalConstructor()
			->getMock();

		$parserOutputHelper = $this->getMockBuilder( 'BootstrapComponents\\ParserOutputHelper' )
			->disableOriginalConstructor()
			->getMock();

		/** @noinspection PhpParamsInspection */
		$instance = new ImageModal( $dummyLinker, $title, $file, $nestingController, $parserOutputHelper );
		$time = false;
		$res = '';
		$fp =  [ 'manualthumb' => 'ImageInvalid.png' ];

		$resultOfParseCall = $instance->parse( $fp, $hp, $time, $res );

		$this->assertTrue(
			$resultOfParseCall
		);
	}

	/**
	 * @throws \MWException
	 * @throws \ConfigException
	 */
	public function testCanParseOnOnInvalidContentImage() {
		$dummyLinker = $this->getMockBuilder( 'DummyLinker' )
			->disableOriginalConstructor()
			->getMock();

		$title = $this->getMockBuilder( 'Title' )
			->disableOriginalConstructor()
			->getMock();
		$title->expects( $this->any() )
			->method( 'getLocalUrl' )
			->willReturn( '/File:Serenity.png' );

		$thumb = $this->getMockBuilder( 'ThumbnailImage' )
			->disableOriginalConstructor()
			->getMock();
		$thumb->expects( $this->any() )
			->method( 'getWidth' )
			->willReturn( 52 );
		$thumb->expects( $this->any() )
			->method( 'toHtml' )
			->will( $this->returnCallback(
				function( $params ) {
					$ret = [];
					foreach ( [ 'alt', 'title', 'img-class' ] as $itemToPrint ) {
						if ( isset( $params[$itemToPrint] ) && $params[$itemToPrint] ) {
							$ret[] = ($itemToPrint != 'img-class' ? $itemToPrint : 'class') . '="' . $params[$itemToPrint] . '"';
						}
					}
					return '<img src=TEST_OUTPUT ' . implode( ' ', $ret ) . '>';
				}
			) );
		$file = $this->getMockBuilder( 'LocalFile' )
			->disableOriginalConstructor()
			->getMock();
		$file->expects( $this->any() )
			->method( 'allowInlineDisplay' )
			->willReturn( true );
		$file->expects( $this->any() )
			->method( 'exists' )
			->willReturn( true );
		$file->expects( $this->any() )
			->method( 'getWidth' )
			->willReturn( 52 );
		$file->expects( $this->any() )
			->method( 'mustRender' )
			->willReturn( false );
		$file->expects( $this->any() )
			->method( 'getUnscaledThumb' )
			->willReturn( false );
		$file->expects( $this->any() )
			->method( 'transform' )
			->willReturn( $thumb );

		$nestingController = $this->getMockBuilder( 'BootstrapComponents\\NestingController' )
			->disableOriginalConstructor()
			->getMock();
		$parserOutputHelper = $this->getMockBuilder( 'BootstrapComponents\\ParserOutputHelper' )
			->disableOriginalConstructor()
			->getMock();

		/** @noinspection PhpParamsInspection */
		$instance = new ImageModal( $dummyLinker, $title, $file, $nestingController, $parserOutputHelper );
		$time = false;
		$res = '';
		$fp = [ 'align' => 'left' ]; # otherwise, this test produces an exception while trying to call $title->getPageLanguage()->alignEnd()

		$resultOfParseCall = $instance->parse( $fp, $hp, $time, $res );

		$this->assertTrue(
			$resultOfParseCall
		);
	}

	/**
	 * @param array  $fp
	 * @param array  $hp
	 * @param string $expectedRegExp
	 *
	 * @dataProvider canParseDataProvider
	 * @throws \MWException
	 * @throws \ConfigException
	 */
	public function testCanParse( $fp, $hp, $expectedRegExp ) {
		$dummyLinker = $this->getMockBuilder( 'DummyLinker' )
			->disableOriginalConstructor()
			->getMock();

		$title = $this->getMockBuilder( 'Title' )
			->disableOriginalConstructor()
			->getMock();
		$title->expects( $this->any() )
			->method( 'getLocalUrl' )
			->willReturn( '/File:Serenity.png' );

		$thumb = $this->getMockBuilder( 'ThumbnailImage' )
			->disableOriginalConstructor()
			->getMock();
		$thumb->expects( $this->any() )
			->method( 'getWidth' )
			->willReturn( 640 );
		$thumb->expects( $this->any() )
			->method( 'toHtml' )
			->will( $this->returnCallback(
				function( $params ) {
					$ret = [];
					foreach ( [ 'alt', 'title', 'img-class' ] as $itemToPrint ) {
						if ( isset( $params[$itemToPrint] ) && $params[$itemToPrint] ) {
							$ret[] = ($itemToPrint != 'img-class' ? $itemToPrint : 'class') . '="' . $params[$itemToPrint] . '"';
						}
					}
					return '<img src=TEST_OUTPUT ' . implode( ' ', $ret ) . '>';
				}
			) );
		$file = $this->getMockBuilder( 'LocalFile' )
			->disableOriginalConstructor()
			->getMock();
		$file->expects( $this->any() )
			->method( 'allowInlineDisplay' )
			->willReturn( true );
		$file->expects( $this->any() )
			->method( 'exists' )
			->willReturn( true );
		$file->expects( $this->any() )
			->method( 'getWidth' )
			->willReturn( 52 );
		$file->expects( $this->any() )
			->method( 'mustRender' )
			->willReturn( false );
		$file->expects( $this->any() )
			->method( 'getUnscaledThumb' )
			->willReturn( $thumb );
		$file->expects( $this->any() )
			->method( 'transform' )
			->willReturn( $thumb );

		$nestingController = $this->getMockBuilder( 'BootstrapComponents\\NestingController' )
			->disableOriginalConstructor()
			->getMock();
		$nestingController->expects( $this->any() )
			->method( 'generateUniqueId' )
			->will( $this->returnCallback(
				function( $component ) {
					return 'bsc_' . $component . '_test';
				}
			) );


		$parserOutputHelper = $this->getMockBuilder( 'BootstrapComponents\\ParserOutputHelper' )
			->disableOriginalConstructor()
			->getMock();

		/** @noinspection PhpParamsInspection */
		$instance = new ImageModal( $dummyLinker, $title, $file, $nestingController, $parserOutputHelper );
		$time = false;
		$res = '';

		$resultOfParseCall = $instance->parse( $fp, $hp, $time, $res );

		/** @noinspection PhpParamsInspection */
		$this->assertEquals(
			$expectedRegExp,
			$resultOfParseCall ? $resultOfParseCall : $res,
			'failed with test data:' . $this->generatePhpCodeForManualProviderDataOneCase( $fp, $hp )
		);
	}

	/**
	 * @throws ConfigException cascading {@see \Config::get}
	 * @return array[]
	 */
	public function canParseDataProvider() {
		$globalConfig = MediaWikiServices::getInstance()->getMainConfig();
		$scriptPath = $globalConfig->get( 'ScriptPath' );
		/*
		 * notes on adding tests:
		 * - when using manual thumbnail, inject $scriptPath: <img alt="" src="' . $scriptPath . '/images/a/aa/Shuttle.png" width="68" height="18" ...
		 * - always supply an align value, otherwise testing will fail with an exception due to bad class design (blame @oetterer)
		 * - switch values (booleans) are true when present and false. see "frameless" on test "manual width, frameless"
		 */
		return [
			'no params' => [
				[],
				[],
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><img src=TEST_OUTPUT class="img-responsive"></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT class="img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'frame params w/o thumbnail' => [
				[
					'align'   => 'left',
					'alt'     => 'test_alt',
					'caption' => 'test_caption',
					'class'   => 'test_class',
					'title'   => 'test_title',
					'vAlign'  => 'text-top',
				],
				[],
				'<div class="floatleft"><span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><img src=TEST_OUTPUT alt="test_alt" title="test_title" class="test_class img-responsive"></span></div><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT alt="test_alt" title="test_title" class="test_class img-responsive"> <div class="modal-caption">test_caption</div></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'manual width, frameless' => [
				[
					'align'     => 'left',
					'frameless' => false,
				],
				[
					'width' => 200,
					'page'  => 7,
				],
				'<div class="floatleft"><span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><img src=TEST_OUTPUT class="img-responsive"></span></div><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT class="img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png?page=7">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'thumbnail, manual width' => [
				[
					'align'     => 'middle',
					'thumbnail' => false,
				],
				[
					'width' => 200,
					'page'  => 7,
				],
				'<div class="thumb tmiddle"><span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumbinner" style="width:642px;"><img src=TEST_OUTPUT class="img-responsive thumbimage">  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></span></div><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT class="img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png?page=7">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'manual thumbnail, framed' => [
				[
					'align'       => 'middle',
					'manualthumb' => 'Shuttle.png',
					'framed'      => false,
				],
				[],
				'<div class="thumb tmiddle"><span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumbinner" style="width:70px;"><img alt="" src="' . $scriptPath . '/images/a/aa/Shuttle.png" width="68" height="18" class="img-responsive thumbimage" />  <div class="thumbcaption"></div></div></span></div><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT class="img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'manual thumbnail, upright' => [
				[
					'align'       => 'left',
					'upright'     => 2342,
					'manualthumb' => 'Shuttle.png',
					'frameless'   => false,
				],
				[],
				'<div class="thumb tleft"><span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumbinner" style="width:70px;"><img alt="" src="' . $scriptPath . '/images/a/aa/Shuttle.png" width="68" height="18" class="img-responsive thumbimage" />  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></span></div><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT class="img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
		];
	}

	/**
	 * @param array $frameParams
	 * @param array $handlerParams
	 *
	 * @return string
	 */
	private function generatePhpCodeForManualProviderDataOneCase( $frameParams, $handlerParams ) {
		$ret = PHP_EOL;
		foreach ( [ 'frameParams', 'handlerParams' ] as $arrayArg ) {
			$ret .= '$' . $arrayArg . ' = [' . PHP_EOL;
			foreach ( $$arrayArg as $key => $val ) {
				$ret .= "\t'" . $key . '\' => ';
				switch ( gettype( $val ) ) {
					case 'boolean' :
						$ret .= $val ? 'true' : 'false';
						break;
					case 'integer' :
						$ret .= $val;
						break;
					default :
						$ret .= '\'' . $val . '\'';
						break;
				}
				$ret .= ',' . PHP_EOL;
			}
			$ret .= '],' . PHP_EOL;
		}
		return $ret;
	}
}
