<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\CarouselGallery;
use BootstrapComponents\ParserRequest;
use \MWException;
use \PHPUnit_Framework_TestCase;
use \Title;

/**
 * @covers  \BootstrapComponents\CarouselGallery
 * @group   bootstrap-components
 *
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  oetterer
 */
class CarouselGalleryTest extends PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\CarouselGallery',
			new CarouselGallery( 'carousel' )
		);
	}

	/**
	 * @param array  $imageList
	 * @param array  $additionalAttributes
	 * @param string $expectedOutput
	 *
	 * @throws MWException
	 * @dataProvider galleryDataProvider
	 */
	public function testToHtml( $imageList, $additionalAttributes, $expectedOutput ) {
		$carousel = $this->getMockBuilder( 'BootstrapComponents\\Component\\Carousel' )
			->disableOriginalConstructor()
			->getMock();
		$carousel->expects( $this->any() )
			->method( 'parseComponent' )
			->will(
				$this->returnCallback(
					function( ParserRequest $parserRequest ) {
						$attributes = $parserRequest->getAttributes();
						array_unshift( $attributes, $parserRequest->getInput() );
						return $attributes;
					}
				)
			);
		$instance = new CarouselGallery( 'carousel' );
		$instance->mParser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();

		foreach ( $imageList as $imageData ) {
			$instance->add( Title::newFromText( $imageData[0] ), $imageData[1], $imageData[2], $imageData[3], $imageData[4] );
		}
		$instance->setAttributes( $additionalAttributes );
		$this->assertEquals(
			$expectedOutput,
			$instance->toHTML( $carousel )
		);
	}

	/**
	 * @return array
	 */
	public function galleryDataProvider() {
		return [
			'simple' => [
				[
					[ 'File:Mal.jpg', 'Malcolm Reynolds', '(alt) Malcolm Reynolds', '', [] ],
					[ 'File:Wash.jpg', 'Hoban Washburne', '', '/List_of_best_Pilots_in_the_Verse', [] ],
					[ 'File:MirandaSecretFiles.pdf', '(c) by Hands of Blue', '', '', [ 'page' => '13', 'float' => 'none' ] ],
				],
				[
					'class' => 'firefly',
					'style' => 'float:space',
					'id'    => 'youcanttakethesky',
				],
				[
					0                                                         => '[[File:Mal.jpg|Malcolm Reynolds|alt=(alt) Malcolm Reynolds]]',
					'[[File:Wash.jpg|Hoban Washburne|link'                    => '/List_of_best_Pilots_in_the_Verse]]',
					'[[File:MirandaSecretFiles.pdf|(c) by Hands of Blue|page' => '13|float=none]]',
					'class'                                                   => 'firefly',
					'style'                                                   => 'float:space',
					'id'                                                      => 'youcanttakethesky',
				],
			],
			# 'no images' => [] cannot be tested due to bad class design. blame @oetterer
		];
	}
}
