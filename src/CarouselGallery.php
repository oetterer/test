<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents;

use BootstrapComponents\Component\Carousel;
use \ImageGalleryBase;
use \MWException;
use \Parser;
use \Title;

/**
 * Class CarouselGallery
 *
 * @package BootstrapComponents
 */
class CarouselGallery extends ImageGalleryBase {

	/**
	 * @param Carousel $carousel used for unit tests
	 *
	 * @throws MWException cascading {@see \BootstrapComponents\Component::parseComponent}
	 * @return string
	 */
	public function toHTML( $carousel = null ) {
		if ( $this->isEmpty() ) {
			return ApplicationFactory::getInstance()->getParserOutputHelper( $this->mParser )
				->renderErrorMessage( 'bootstrap-components-carousel-images-missing' );
		}
		if ( !$carousel || !$carousel instanceof Carousel ) {
			$carousel = new Carousel(
				ApplicationFactory::getInstance()->getComponentLibrary(),
				ApplicationFactory::getInstance()->getParserOutputHelper( $this->mParser ),
				ApplicationFactory::getInstance()->getNestingController()
			);
		}
		$carouselAttributes = $this->convertImages(
			$this->getImages(),
			$this->mParser,
			$this->mHideBadImages,
			$this->getContextTitle()
		);
		if ( !count( $carouselAttributes ) ) {
			return ApplicationFactory::getInstance()->getParserOutputHelper( $this->mParser )
				->renderErrorMessage( 'bootstrap-components-carousel-images-missing' );
		}
		$carouselAttributes = $this->addAttributes( $carouselAttributes, $this->mAttribs );
		array_unshift( $carouselAttributes, $this->mParser );
		$carouselParserRequest = ApplicationFactory::getInstance()->getNewParserRequest(
			$carouselAttributes,
			ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION
		);
		return $carousel->parseComponent( $carouselParserRequest );
	}

	/**
	 * @param array $origAttributes
	 * @param array $localAttributes
	 *
	 * @return array
	 */
	private function addAttributes( $origAttributes, $localAttributes ) {
		if ( !$localAttributes ) {
			return $origAttributes;
		}
		// add $localAttributes to $origAttributes's attributes. note the difference in parameter handling
		foreach ( $localAttributes as $key => $val ) {
			$origAttributes[] = $key . '=' . $val;
		}
		return $origAttributes;
	}

	/**
	 * @param        $imageList
	 * @param Parser $parser
	 * @param bool   $hideBadImages
	 * @param bool   $contextTitle
	 *
	 * @return array
	 */
	private function convertImages( $imageList, $parser = null, $hideBadImages = true, $contextTitle = false ) {
		$newImageList = [];
		foreach ( $imageList as $imageData ) {
			/** @var Title $imageTitle */
			$imageTitle = $imageData[0];

			if ( $imageTitle->getNamespace() !== NS_FILE ) {
				if ( $parser instanceof Parser ) {
					$parser->addTrackingCategory( 'broken-file-category' );
				}
				continue;
			} elseif ( $hideBadImages && wfIsBadImage( $imageTitle->getDBkey(), $contextTitle ) ) {
				continue;
			}

			$carouselImage = $this->buildImageStringFromData( $imageData );
			$newImageList[] = $carouselImage;
		}
		return $newImageList;
	}

	/**
	 * @param array $imageData
	 *
	 * @return string
	 */
	private function buildImageStringFromData( $imageData ) {

		/** @var Title $imageTitle */
		list( $imageTitle, $imageCaption, $imageAlt, $imageLink, $imageParams ) = $imageData;
		// note that imageCaption, imageAlt and imageLink are strings. the latter is a local link or empty
		// imageParams is an associative array param => value
		$carouselImage = '[[' . $imageTitle->getPrefixedText();
		if ( $imageCaption ) {
			$carouselImage .= '|' . $imageCaption;
		}
		if ( $imageAlt ) {
			$carouselImage .= '|alt=' . $imageAlt;
		}
		if ( $imageLink ) {
			# @note: this is a local link. has to be an article name :(
			# @fixme: assuming, that the correct link processing is done in image processing
			$carouselImage .= '|link=' . $imageLink;
		}
		if ( $imageParams ) {
			foreach ( $imageParams as $key => $val ) {
				$carouselImage .= '|' . $key . '=' . $val;
			}
		}
		$carouselImage .= ']]';

		return $carouselImage;
	}
}