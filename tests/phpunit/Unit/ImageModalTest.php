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
	 * @param int    $num
	 * @param string $expectedRegExp
	 *
	 * @dataProvider manualCanParseDataProvider
	 * @throws \MWException
	 * @throws \ConfigException
	 */
	public function testCanParse( $fp, $hp, $num, $expectedRegExp ) {
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
			'failed with test#' . $this->generatePhpCodeForManualProviderDataOneCase( $num, $fp, $hp )
		);
	}

	/**
	 * @throws ConfigException cascading {@see \Config::get}
	 * @return array[]
	 */
	public function manualCanParseDataProvider() {
		$globalConfig = MediaWikiServices::getInstance()->getMainConfig();
		$scriptPath = $globalConfig->get( 'ScriptPath' );
		return [
			'29752'  => [
				[
					'thumbnail' => false,
					'frameless' => false,
					'upright'   => 42,
					'align'     => 'left',
					'alt'       => 'test_alt',
					'class'     => 'test_class',
					'title'     => 'test_title',
					'vAlign'    => 'text-top',
				],
				[
				],
				29752,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumb tleft"><div class="thumbinner" style="width:642px;"><img src=TEST_OUTPUT alt="test_alt" title="test_title" class="test_class thumbimage">  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT alt="test_alt" title="test_title" class="test_class img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'30243'  => [
				[
					'thumbnail' => false,
					'frameless' => false,
					'upright'   => 42,
					'align'     => 'right',
					'alt'       => 'test_alt',
					'vAlign'    => 'top',
				],
				[
					'width' => 100,
					'page'  => 7,
				],
				30243,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumb tright"><div class="thumbinner" style="width:642px;"><img src=TEST_OUTPUT alt="test_alt" class="thumbimage">  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT alt="test_alt" class="img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png?page=7">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'33404'  => [
				[
					'thumbnail' => false,
					'align'     => 'center',    # this deviates from the generated test #33404
					'alt'       => 'test_alt',
					'caption'   => 'test_caption',
					'class'     => 'test_class',
					'title'     => 'test_title',
					'vAlign'    => 'text-bottom',
				],
				[
				],
				33404,
				'<div class="center"><span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumb tnone"><div class="thumbinner" style="width:642px;"><img src=TEST_OUTPUT alt="test_alt" title="test_title" class="test_class thumbimage">  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div>test_caption</div></div></div></span></div><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT alt="test_alt" title="test_title" class="test_class img-responsive"> <div class="modal-caption">test_caption</div></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'39782'  => [
				[
					'thumbnail' => false,
					'upright'   => 42,
					'align'     => 'left',
					'alt'       => 'test_alt',
					'caption'   => 'test_caption',
				],
				[
					'width' => 100,
				],
				39782,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumb tleft"><div class="thumbinner" style="width:642px;"><img src=TEST_OUTPUT alt="test_alt" class="thumbimage">  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div>test_caption</div></div></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT alt="test_alt" class="img-responsive"> <div class="modal-caption">test_caption</div></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'47803'  => [
				[
					'thumbnail'   => false,
					'framed'      => false,
					'frameless'   => false,
					'manualthumb' => 'Shuttle.png',
					'upright'     => 0,
					'align'       => 'right',
					'alt'         => 'test_alt',
					'class'       => 'test_class',
					'title'       => 'test_title',
					'vAlign'      => 'text-bottom',
				],
				[
					'width' => 100,
					'page'  => 7,
				],
				47803,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumb tright"><div class="thumbinner" style="width:70px;"><img alt="test_alt" src="' . $scriptPath . '/images/a/aa/Shuttle.png" title="test_title" width="68" height="18" class="test_class thumbimage" />  <div class="thumbcaption"></div></div></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT alt="test_alt" title="test_title" class="test_class img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png?page=7">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			# this deviates from the generated test, manualthumb removed
			'48727'  => [
				[
					'thumbnail' => false,
					'framed'    => false,
					'frameless' => false,
					'border'    => false,
					'upright'   => 42,
					'align'     => 'left',
					'caption'   => 'test_caption',
					'class'     => 'test_class',
					'vAlign'    => 'text-top',
				],
				[
					'width' => 100,
					'page'  => 7,
				],
				48727,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumb tleft"><div class="thumbinner" style="width:642px;"><img src=TEST_OUTPUT class="test_class thumbimage">  <div class="thumbcaption">test_caption</div></div></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT class="test_class img-responsive"> <div class="modal-caption">test_caption</div></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png?page=7">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'57858'  => [
				[
					'thumbnail'   => false,
					'framed'      => false,
					'manualthumb' => 'Shuttle.png',
					'upright'     => 0,
					'align'       => 'right',
					'vAlign'      => 'top',
				],
				[
					'width' => 100,
				],
				57858,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumb tright"><div class="thumbinner" style="width:70px;"><img alt="" src="' . $scriptPath . '/images/a/aa/Shuttle.png" width="68" height="18" class="thumbimage" />  <div class="thumbcaption"></div></div></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT class="img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			# this deviates from the generated test, manualthumb removed
			'58668'  => [
				[
					'thumbnail' => false,
					'framed'    => false,
					'upright'   => 0,
					'align'     => 'none',
					'alt'       => 'test_alt',
					'caption'   => 'test_caption',
					'title'     => 'test_title',
					'vAlign'    => 'bottom',
				],
				[
				],
				58668,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumb tnone"><div class="thumbinner" style="width:642px;"><img src=TEST_OUTPUT alt="test_alt" title="test_title" class="thumbimage">  <div class="thumbcaption">test_caption</div></div></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT alt="test_alt" title="test_title" class="img-responsive"> <div class="modal-caption">test_caption</div></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'74994'  => [
				[
					'thumbnail'   => false,
					'manualthumb' => 'Shuttle.png',
					'align'       => 'right',
					'alt'         => 'test_alt',
					'class'       => 'test_class',
					'vAlign'      => 'baseline',
				],
				[
					'width' => 100,
				],
				74994,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumb tright"><div class="thumbinner" style="width:70px;"><img alt="test_alt" src="' . $scriptPath . '/images/a/aa/Shuttle.png" width="68" height="18" class="test_class thumbimage" />  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT alt="test_alt" class="test_class img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'78344'  => [
				[
					'thumbnail'   => false,
					'manualthumb' => 'Shuttle.png',
					'upright'     => 0,
					'align'       => 'right',
					'title'       => 'test_title',
				],
				[
				],
				78344,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumb tright"><div class="thumbinner" style="width:70px;"><img alt="" src="' . $scriptPath . '/images/a/aa/Shuttle.png" title="test_title" width="68" height="18" class="thumbimage" />  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT title="test_title" class="img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			# this deviates from the generated test, manualthumb removed
			'80460'  => [
				[
					'thumbnail' => false,
					'border'    => false,
					'upright'   => 42,
					'align'     => 'right',
					'caption'   => 'test_caption',
					'title'     => 'test_title',
					'vAlign'    => 'middle',
				],
				[
				],
				80460,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumb tright"><div class="thumbinner" style="width:642px;"><img src=TEST_OUTPUT title="test_title" class="thumbimage">  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div>test_caption</div></div></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT title="test_title" class="img-responsive"> <div class="modal-caption">test_caption</div></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'81595'  => [
				[
					'thumbnail'   => false,
					'manualthumb' => 'Shuttle.png',
					'upright'     => 42,
					'align'       => 'left',
					'alt'         => 'test_alt',
					'class'       => 'test_class',
					'title'       => 'test_title',
					'vAlign'      => 'text-top',
				],
				[
					'width' => 100,
					'page'  => 7,
				],
				81595,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumb tleft"><div class="thumbinner" style="width:70px;"><img alt="test_alt" src="' . $scriptPath . '/images/a/aa/Shuttle.png" title="test_title" width="68" height="18" class="test_class thumbimage" />  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT alt="test_alt" title="test_title" class="test_class img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png?page=7">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'97100'  => [
				[
					'framed'  => false,
					'border'  => false,
					'upright' => 0,
					'align'   => 'left',
					'caption' => 'test_caption',
					'title'   => 'test_title',
					'vAlign'  => 'text-top',
				],
				[
				],
				97100,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumb tleft"><div class="thumbinner" style="width:642px;"><img src=TEST_OUTPUT title="test_title" class="thumbimage">  <div class="thumbcaption">test_caption</div></div></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT title="test_title" class="img-responsive"> <div class="modal-caption">test_caption</div></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'101150' => [
				[
					'framed'  => false,
					'border'  => false,
					'upright' => 42,
					'align'   => 'right',
					'caption' => 'test_caption',
					'class'   => 'test_class',
					'title'   => 'test_title',
					'vAlign'  => 'text-top',
				],
				[
					'width' => 100,
				],
				101150,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumb tright"><div class="thumbinner" style="width:642px;"><img src=TEST_OUTPUT title="test_title" class="test_class thumbimage">  <div class="thumbcaption">test_caption</div></div></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT title="test_title" class="test_class img-responsive"> <div class="modal-caption">test_caption</div></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'117137' => [
				[
					'align'  => 'none',
					'class'  => 'test_class',
					'vAlign' => 'super',
				],
				[
					'page' => 7,
				],
				117137,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="floatnone"><img src=TEST_OUTPUT class="test_class"></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT class="test_class img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png?page=7">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			# this deviates from the generated test, manualthumb removed
			'125313' => [
				[
					'framed'    => false,
					'frameless' => false,
					'border'    => false,
					'align'     => 'right',
					'vAlign'    => 'text-top',
				],
				[
					'page' => 7,
				],
				125313,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumb tright"><div class="thumbinner" style="width:642px;"><img src=TEST_OUTPUT class="thumbimage">  <div class="thumbcaption"></div></div></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT class="img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png?page=7">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'150784' => [
				[
					'frameless'   => false,
					'manualthumb' => 'Shuttle.png',
					'upright'     => 0,
					'align'       => 'left',
					'vAlign'      => 'bottom',
				],
				[
				],
				150784,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumb tleft"><div class="thumbinner" style="width:70px;"><img alt="" src="' . $scriptPath . '/images/a/aa/Shuttle.png" width="68" height="18" class="thumbimage" />  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT class="img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			# this deviates from the generated test, manualthumb removed
			'151538' => [
				[
					'frameless' => false,
					'upright'   => 0,
					'align'     => 'none',
					'alt'       => 'test_alt',
					'class'     => 'test_class',
				],
				[
					'width' => 100,
				],
				151538,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="floatnone"><img src=TEST_OUTPUT alt="test_alt" class="test_class"></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT alt="test_alt" class="test_class img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			# this deviates from the generated test, manualthumb removed
			'157104' => [
				[
					'border'      => false,
					'align'       => 'none',
					'alt'         => 'test_alt',
					'class'       => 'test_class',
					'vAlign'      => 'middle',
				],
				[
				],
				157104,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="floatnone"><img src=TEST_OUTPUT alt="test_alt" class="test_class thumbborder"></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT alt="test_alt" class="test_class img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'160354' => [
				[
					'border'      => false,
					'manualthumb' => 'Shuttle.png',
					'upright'     => 0,
					'align'       => 'none',
					'alt'         => 'test_alt',
					'vAlign'      => 'super',
				],
				[
					'width' => 100,
				],
				160354,
				'<span class="modal-trigger" data-toggle="modal" data-target="#bsc_image_modal_test"><div class="thumb tnone"><div class="thumbinner" style="width:70px;"><img alt="test_alt" src="' . $scriptPath . '/images/a/aa/Shuttle.png" width="68" height="18" class="thumbimage" />  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></div></span><div class="modal fade" role="dialog" id="bsc_image_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT alt="test_alt" class="img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
		];
	}

	/**
	 * @param int   $num
	 * @param array $fp
	 * @param array $hp
	 *
	 * @return string
	 */
	private function generatePhpCodeForManualProviderDataOneCase( $num, $fp, $hp ) {
		$ret = '\'' . $num . '\' => [' . PHP_EOL;
		foreach ( [ $fp, $hp ] as $arrayArg ) {
			$ret .= "\t" . '[' . PHP_EOL;
			foreach ( $arrayArg as $key => $val ) {
				$ret .= "\t\t'" . $key . '\' => ';
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
			$ret .= "\t" . '],' . PHP_EOL;
		}
		$ret .= "\t" . $num . ',' . PHP_EOL;
		$ret .= "\t" . '\'\',' . PHP_EOL;
		return $ret . '],' . PHP_EOL;
	}
}
