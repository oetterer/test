<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents\Component;

use BootstrapComponents\Component;
use BootstrapComponents\ParserRequest;
use \Html;

/**
 * Class Carousel
 *
 * Class for component 'carousel'
 *
 * @package BootstrapComponents
 */
class Carousel extends Component {
	/**
	 * @inheritdoc
	 *
	 * @param ParserRequest $parserRequest
	 */
	public function placeMe( $parserRequest ) {
		$images = $this->extractAndParseImageList( $parserRequest );
		if ( !count( $images ) ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-carousel-images-missing' );
		}

		$class = [ 'carousel', 'slide' ];
		list ( $class, $style ) = $this->processCss( $class, [], $parserRequest->getAttributes() );

		return [
			Html::rawElement(
				'div',
				[
					'class'     => $this->arrayToString( $class, ' ' ),
					'style'     => $this->arrayToString( $style, ';' ),
					'id'        => $this->getId(),
					'data-ride' => 'carousel',
				],
				$this->generateIndicators( count( $images ) )
				. Html::rawElement(
					'div',
					[ 'class' => 'carousel-inner' ],
					$this->itemize( $images )
				)
				. $this->buildControls()
			),
			"isHTML"  => true,
			"noparse" => true,
		];
	}

	/**
	 * Responsible for generating the a tags that make up the prev and next controls
	 *
	 * @return string
	 */
	private function buildControls() {
		return Html::rawElement(
				'a',
				[
					'class'      => 'left carousel-control',
					'href'       => '#' . $this->getId(),
					'data-slide' => 'prev',
				],
				Html::rawElement( 'span', [ 'class' => 'glyphicon glyphicon-chevron-left' ] )
			) . Html::rawElement(
				'a',
				[
					'class'      => 'right carousel-control',
					'href'       => '#' . $this->getId(),
					'data-slide' => 'next',
				],
				Html::rawElement( 'span', [ 'class' => 'glyphicon glyphicon-chevron-right' ] )
			);
	}

	/**
	 * Extracts and parsed all images for the carousel
	 *
	 * @param ParserRequest $parserRequest
	 *
	 * @return string[]
	 */
	private function extractAndParseImageList( ParserRequest $parserRequest ) {
		$elements = [];
		if ( $parserRequest->getInput() ) {
			$elements[$parserRequest->getInput()] = true;
		}
		$elements = array_merge( $elements, $parserRequest->getAttributes() );
		$images = [];
		foreach ( $elements as $key => $val ) {
			$string = $key . (is_bool( $val ) ? '' : '=' . $val);
			if ( preg_match( '/\[.+\]/', $string ) ) {
				// we assume an image, local or remote
				$images[] = $parserRequest->getParser()->recursiveTagParse(
					$string,
					$parserRequest->getFrame()
				);
			}
		}
		return $images;
	}

	/**
	 * @param int $num
	 *
	 * @return string
	 */
	private function generateIndicators( $num ) {
		$inner = PHP_EOL;
		$class = 'active';
		for ( $i = 0; $i < $num; $i++ ) {
			$inner .= "\t" . Html::rawElement(
					'li',
					[
						'data-target'   => '#' . $this->getId(),
						'data-slide-to' => $i,
						'class'         => $class,
					]
				) . PHP_EOL;
			$class = false;
		}
		return PHP_EOL . Html::rawElement(
				'ol',
				[ 'class' => 'carousel-indicators' ],
				$inner
			) . PHP_EOL;
	}

	/**
	 * @param string[] $images
	 *
	 * @return string
	 */
	private function itemize( $images ) {
		$slides = PHP_EOL;
		$active = ' active';
		foreach ( $images as $image ) {
			$slides .= "\t" . Html::rawElement(
					'div',
					[ 'class' => 'item' . $active ],
					$image
				) . PHP_EOL;
			$active = '';
		}
		return $slides;
	}
}