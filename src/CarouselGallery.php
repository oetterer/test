<?php
/**
 * Contains the class providing and rendering the carousel gallery mode.
 *
 * @copyright (C) 2018, Tobias Oetterer, University of Paderborn
 * @license       https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 (or later)
 *
 * This file is part of the MediaWiki extension BootstrapComponents.
 * The BootstrapComponents extension is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The BootstrapComponents extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @file
 * @ingroup       BootstrapComponents
 * @author        Tobias Oetterer
 */

namespace BootstrapComponents;

use BootstrapComponents\Component\Carousel;
use \ImageGalleryBase;

/**
 * Class CarouselGallery
 *
 * @since 1.0
 */
class CarouselGallery extends ImageGalleryBase {

	/**
	 * @param ParserOutputHelper $parserOutputHelper used for unit tests
	 *
	 * @throws \MWException cascading {@see \BootstrapComponents\Component::parseComponent}
	 * @return string
	 */
	public function toHTML( $parserOutputHelper = null ) {
		$parserOutputHelper = is_null( $parserOutputHelper )
			? ApplicationFactory::getInstance()->getParserOutputHelper( $this->mParser )
			: $parserOutputHelper;
		if ( $this->isEmpty() ) {
			return $parserOutputHelper->renderErrorMessage( 'bootstrap-components-carousel-images-missing' );
		}
		$carousel = new Carousel(
			ApplicationFactory::getInstance()->getComponentLibrary(),
			$parserOutputHelper,
			ApplicationFactory::getInstance()->getNestingController()
		);
		$carouselAttributes = $this->convertImages(
			$this->getImages(),
			$this->mParser,
			$this->mHideBadImages,
			$this->getContextTitle()
		);
		if ( !count( $carouselAttributes ) ) {
			return $parserOutputHelper->renderErrorMessage( 'bootstrap-components-carousel-images-missing' );
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
		if ( empty( $localAttributes ) ) {
			return $origAttributes;
		}
		// add $localAttributes to $origAttributes's attributes. note the difference in parameter handling
		foreach ( $localAttributes as $key => $val ) {
			$origAttributes[] = $key . '=' . $val;
		}
		return $origAttributes;
	}

	/**
	 * @param         $imageList
	 * @param \Parser $parser
	 * @param bool    $hideBadImages
	 * @param bool    $contextTitle
	 *
	 * @return array
	 */
	private function convertImages( $imageList, $parser = null, $hideBadImages = true, $contextTitle = false ) {
		$newImageList = [];
		foreach ( $imageList as $imageData ) {
			/** @var \Title $imageTitle */
			$imageTitle = $imageData[0];

			if ( $imageTitle->getNamespace() !== NS_FILE ) {
				if ( is_a( $parser, 'Parser' ) ) {
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

		/** @var \Title $imageTitle */
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
			# @note: assuming here, that the correct link processing is done in image processing
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