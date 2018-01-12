<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\ImageModal;
use \Linker;
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
		#echo count( $this->origThumbAndModalTriggerCompareAllCaseProvider() ); // 165888
		#echo $this->generatePhpCodeForManualProviderData( 20 );
	}

	/**
	 * @param int $numTests
	 */
	public function testSomeRandomCompareTriggerWithOriginalThumb( $numTests = self::NUM_OF_RND_COMPARE_TESTS ) {
		$testCases = $this->origThumbAndModalTriggerCompareAllCaseProvider();
		for ( $i = 0; $i < $numTests; $i++ ) {
			$testCaseNum = array_rand( array_keys( $testCases ) );
			$this->doTestCompareTriggerWithOriginalThumb( $testCaseNum[0], $testCaseNum[1], $testCaseNum );
		}
	}

	/**
	 * @param int   $testCaseNum
	 * @param array $testCaseData
	 */
	public function testSingleCompareTriggerWithOriginalThumb( $testCaseData = null, $testCaseNum = null ) {
		if ( !$testCaseData ) {
			$testCases = $this->origThumbAndModalTriggerCompareAllCaseProvider();
			$manualTestCaseNum = false;

			$testCaseNum = $manualTestCaseNum !== false ? $manualTestCaseNum : array_rand( array_keys( $testCases ) );
			$testCaseData = $testCases[$testCaseNum];
		}
		list( $fp, $hp ) = $testCaseData;
		$this->doTestCompareTriggerWithOriginalThumb( $fp, $hp, $testCaseNum );
	}


	/**
	 * As of version 1.0, this would execute 165888 tests.
	 *
	 * If you really want to run this, make sure you execute
	 *      export COMPOSER_PROCESS_TIMEOUT=50000
	 * on cli before the composer call
	 *
	 * @param array $fp
	 * @param array $hp
	 * @param int   $num
	 *
	 * @dataProvider origThumbAndModalTriggerCompareAllCaseProvider
	 */
	public function _testAllCompareTriggerWithOriginalThumbAllCases( $fp, $hp, $num = null ) {
		$this->doTestCompareTriggerWithOriginalThumb( $fp, $hp, $num );
	}


	/**
	 * @param array $fp
	 * @param array $hp
	 * @param int   $num
	 *
	 * @dataProvider origThumbAndModalTriggerCompareManualProvider
	 */
	public function _testAllCompareTriggerWithOriginalThumbManualCases( $fp, $hp, $num = null ) {
		$this->doTestCompareTriggerWithOriginalThumb( $fp, $hp, $num );
	}


	/**
	 * @param array $fp
	 * @param array $hp
	 * @param int   $num
	 */
	public function doTestCompareTriggerWithOriginalThumb( $fp, $hp, $num = null ) {

		$fp['phpunit_uut'] = true;
		$fp['no-link'] = true;
		$hp['page'] = isset( $hp['page'] ) ? $hp['page'] : false;

		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();

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
			->will( $this->returnCallback( function( $params ) {
				$ret = [];
				foreach ( [ 'alt', 'title', 'img-class' ] as $itemToPrint ) {
					if ( isset( $params[$itemToPrint] ) && $params[$itemToPrint] ) {
						$ret[] = $itemToPrint . '="' . $params[$itemToPrint] . '"';
					}
				}
				return '<img src=TEST_OUTPUT ' . implode( ' ', $ret ) . '>';
			}
			)
			);
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

		/** @noinspection PhpParamsInspection */
		$instance = new ImageModal( $dummyLinker, $title, $file );

		/** @noinspection PhpParamsInspection */
		$this->assertEquals(
			str_replace( ' href="/File:Serenity.png"', '',
			             str_replace( ' href="/File:Serenity.png?page=7"', '',
			                          Linker::makeImageLink( $parser, $title, $file, $fp, $hp )
			             )
			),
			$instance->generateTrigger( $file, $instance->sanitizeFrameParams( $fp ), $hp ),
			'failed with test#' . $this->generatePhpCodeForManualProviderDataOneCase( $num, $fp, $hp )
		);
	}

	/**
	 * @param array  $fp
	 * @param array  $hp
	 * @param int    $num
	 * @param string $expectedRegExp
	 *
	 * @dataProvider origThumbAndModalTriggerCompareManualProvider
	 * @throws \MWException
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
			->willReturn( $thumb );
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

		$resultOfParseCall = $instance->parse( $fp, $hp, $time, $res );
		/** @noinspection PhpParamsInspection */
		$this->assertEquals(
			$expectedRegExp,
			$resultOfParseCall ? $resultOfParseCall : $res,
#			$res,
			'failed with test#' . $this->generatePhpCodeForManualProviderDataOneCase( $num, $fp, $hp )
		);
	}

	/**
	 * @return array
	 */
	public function origThumbAndModalTriggerCompareAllCaseProvider() {
		# @note upright_factor is not used, neither inside \Linker::makeThumbLink2, nor $instance->generateTrigger
		$ret = [];
		foreach ( [ true, false ] as $thumbnail ) {
			foreach ( [ false, 'Shuttle.png' ] as $manualthumb ) {
				foreach ( [ true, false ] as $framed ) {
					foreach ( [ true, false ] as $frameless ) {
						foreach ( [ false, 0, 42 ] as $upright ) {
							foreach ( [ true, false ] as $border ) {
								// false alignment will cause call to $title ->getPageLanguage()->alignEnd() in \Bootstrap\Components\ImageModal::generateTriggerWrapAndFinalize
								// center alignments differ in build
								foreach ( [ "left", "right", "none" ] as $align ) {
									foreach ( [ false, "baseline", "sub", "super", "top", "text-top", "middle", "bottom", "text-bottom" ] as $vAlign ) {
										foreach ( [ false, 'test_alt' ] as $alt ) {
											foreach ( [ false, 'test_class' ] as $class ) {
												foreach ( [ false, 'test_title' ] as $title ) {
													foreach ( [ false, 'test_caption' ] as $caption ) {
														foreach ( [ false, 100 ] as $width ) {
															foreach ( [ false, 7 ] as $page ) {
																$ret[] = $this->addTestCaseToProvider(
																	$thumbnail, $manualthumb, $framed, $frameless, $upright, $border, $align, $vAlign, $alt,
																	$class, $title, $caption, $width, $page
																);
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
		return $ret;
	}

	/**
	 * @return array[]
	 */
	private function addTestCaseToProvider() {
		/** @noinspection PhpUnusedLocalVariableInspection */
		list( $thumbnail, $manualthumb, $framed, $frameless, $upright, $border, $align, $vAlign, $alt, $class, $title, $caption, $width, $page ) = func_get_args();
		$frameParams = [];
		foreach ( [ 'thumbnail', 'framed', 'frameless', 'border' ] as $boolField ) {
			if ( $$boolField ) {
				$frameParams[$boolField] = false;
			}
		}
		foreach ( [ 'manualthumb', 'upright', 'align', 'alt', 'caption', 'class', 'title', 'vAlign' ] as $valueFields ) {
			if ( $$valueFields !== false ) {
				$frameParams[$valueFields] = $$valueFields;
			}
		}
		$handlerParams = [];
		foreach ( [ 'width', 'page' ] as $valueFields ) {
			if ( $$valueFields ) {
				$handlerParams[$valueFields] = $$valueFields;
			}
		}
		return [ $frameParams, $handlerParams ];
	}

	/**
	 * @return array[]
	 */
	public function origThumbAndModalTriggerCompareManualProvider() {
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
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tleft"><div class="thumbinner" style="width:54px;"><img src=TEST_OUTPUT alt="test_alt" title="test_title" class="test_class thumbimage">  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
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
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tright"><div class="thumbinner" style="width:54px;"><img src=TEST_OUTPUT alt="test_alt" class="thumbimage">  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
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
				'<div class="center"><span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tnone"><div class="thumbinner" style="width:54px;"><img src=TEST_OUTPUT alt="test_alt" title="test_title" class="test_class thumbimage">  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div>test_caption</div></div></div></span></div><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
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
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tleft"><div class="thumbinner" style="width:54px;"><img src=TEST_OUTPUT alt="test_alt" class="thumbimage">  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div>test_caption</div></div></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
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
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tright"><div class="thumbinner" style="width:70px;"><img alt="test_alt" src="/images/a/aa/Shuttle.png" title="test_title" width="68" height="18" class="test_class thumbimage" />  <div class="thumbcaption"></div></div></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT alt="test_alt" title="test_title" class="test_class img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png?page=7">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'48727'  => [
				[
					'thumbnail'   => false,
					'framed'      => false,
					'frameless'   => false,
					'border'      => false,
#					'manualthumb' => 'Shuttle.png',    # this deviates from the generated test
					'upright'     => 42,
					'align'       => 'left',
					'caption'     => 'test_caption',
					'class'       => 'test_class',
					'vAlign'      => 'text-top',
				],
				[
					'width' => 100,
					'page'  => 7,
				],
				48727,
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tleft"><div class="thumbinner" style="width:54px;"><img src=TEST_OUTPUT class="test_class thumbimage">  <div class="thumbcaption">test_caption</div></div></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
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
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tright"><div class="thumbinner" style="width:70px;"><img alt="" src="/images/a/aa/Shuttle.png" width="68" height="18" class="thumbimage" />  <div class="thumbcaption"></div></div></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT class="img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'58668'  => [
				[
					'thumbnail'   => false,
					'framed'      => false,
#					'manualthumb' => 'Shuttle.png',    # this deviates from the generated test
					'upright'     => 0,
					'align'       => 'none',
					'alt'         => 'test_alt',
					'caption'     => 'test_caption',
					'title'       => 'test_title',
					'vAlign'      => 'bottom',
				],
				[
				],
				58668,
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tnone"><div class="thumbinner" style="width:54px;"><img src=TEST_OUTPUT alt="test_alt" title="test_title" class="thumbimage">  <div class="thumbcaption">test_caption</div></div></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
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
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tright"><div class="thumbinner" style="width:70px;"><img alt="test_alt" src="/images/a/aa/Shuttle.png" width="68" height="18" class="test_class thumbimage" />  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
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
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tright"><div class="thumbinner" style="width:70px;"><img alt="" src="/images/a/aa/Shuttle.png" title="test_title" width="68" height="18" class="thumbimage" />  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT title="test_title" class="img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'80460'  => [
				[
					'thumbnail'   => false,
					'border'      => false,
#					'manualthumb' => 'Shuttle.png',    # this deviates from the generated test
					'upright'     => 42,
					'align'       => 'right',
					'caption'     => 'test_caption',
					'title'       => 'test_title',
					'vAlign'      => 'middle',
				],
				[
				],
				80460,
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tright"><div class="thumbinner" style="width:54px;"><img src=TEST_OUTPUT title="test_title" class="thumbimage">  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div>test_caption</div></div></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
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
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tleft"><div class="thumbinner" style="width:70px;"><img alt="test_alt" src="/images/a/aa/Shuttle.png" title="test_title" width="68" height="18" class="test_class thumbimage" />  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
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
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tleft"><div class="thumbinner" style="width:54px;"><img src=TEST_OUTPUT title="test_title" class="thumbimage">  <div class="thumbcaption">test_caption</div></div></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
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
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tright"><div class="thumbinner" style="width:54px;"><img src=TEST_OUTPUT title="test_title" class="test_class thumbimage">  <div class="thumbcaption">test_caption</div></div></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
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
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="floatnone"><img src=TEST_OUTPUT class="test_class"></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT class="test_class img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png?page=7">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'125313' => [
				[
					'framed'      => false,
					'frameless'   => false,
					'border'      => false,
#					'manualthumb' => 'Shuttle.png',    # this deviates from the generated test
					'align'       => 'right',
					'vAlign'      => 'text-top',
				],
				[
					'page' => 7,
				],
				125313,
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tright"><div class="thumbinner" style="width:54px;"><img src=TEST_OUTPUT class="thumbimage">  <div class="thumbcaption"></div></div></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
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
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tleft"><div class="thumbinner" style="width:70px;"><img alt="" src="/images/a/aa/Shuttle.png" width="68" height="18" class="thumbimage" />  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT class="img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
			'151538' => [
                [
	                'frameless' => false,
#					'manualthumb' => 'Shuttle.png',     # this deviates from the generated test
	                'upright'   => 0,
	                'align'     => 'none',
	                'alt'       => 'test_alt',
	                'class'     => 'test_class',
                ],
                [
	                'width' => 100,
                ],
                151538,
                '<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="floatnone"><img src=TEST_OUTPUT alt="test_alt" class="test_class"></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
                . '<div class="modal-body"><img src=TEST_OUTPUT alt="test_alt" class="test_class img-responsive"></div>' . PHP_EOL
                . '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
                . '</div></div></div>',
			],
			'157104' => [
				[
					'border'      => false,
					'manualthumb' => 'Shuttle.png',
					'align'       => 'none',
					'alt'         => 'test_alt',
					'class'       => 'test_class',
					'vAlign'      => 'middle',
				],
				[
				],
				157104,
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tnone"><div class="thumbinner" style="width:70px;"><img alt="test_alt" src="/images/a/aa/Shuttle.png" width="68" height="18" class="test_class thumbimage" />  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
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
				'<span class="modal-trigger" data-toggle="modal" data-target="#"><div class="thumb tnone"><div class="thumbinner" style="width:70px;"><img alt="test_alt" src="/images/a/aa/Shuttle.png" width="68" height="18" class="thumbimage" />  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></div></span><div class="modal fade" role="dialog" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>' . PHP_EOL
				. '<div class="modal-body"><img src=TEST_OUTPUT alt="test_alt" class="img-responsive"></div>' . PHP_EOL
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div>' . PHP_EOL
				. '</div></div></div>',
			],
		];
	}

	/**
	 * @param int $num
	 *
	 * @return string
	 *
	 */
	private function generatePhpCodeForManualProviderData( $num = 100 ) {
		$ret = '';
		$allTestData = $this->origThumbAndModalTriggerCompareAllCaseProvider();
		$testCaseNums = array_rand( array_keys( $allTestData ), $num );
		foreach ( $testCaseNums as $testCaseNum ) {
			$ret .= $this->generatePhpCodeForManualProviderDataOneCase( $testCaseNum, $allTestData[$testCaseNum][0], $allTestData[$testCaseNum][1] );
		}
		return $ret;
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
