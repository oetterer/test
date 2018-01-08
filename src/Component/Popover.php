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
use \MWException;

/**
 * Class Popover
 *
 * Class for component 'popover'
 *
 * @package BootstrapComponents
 */
class Popover extends Component {
	/**
	 * @inheritdoc
	 *
	 * @param ParserRequest $parserRequest
	 */
	public function placeMe( $parserRequest ) {
		$attributes = $parserRequest->getAttributes();
		$parser = $parserRequest->getParser();

		if ( isset( $attributes['heading'] ) && strlen( $attributes['heading'] ) ) {
			$heading = $parser->recursiveTagParse( $attributes['heading'] );
		} else {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-popover-heading-missing' );
		}
		if ( isset( $attributes['text'] ) && strlen( $attributes['text'] ) ) {
			$text = $parser->recursiveTagParse( $attributes['text'] );
		} else {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-popover-text-missing' );
		}

		list ( $class, $style ) = $this->processCss(
			$this->calculateClassFrom( $attributes ),
			[],
			$attributes
		);

		return Html::rawElement(
			'button',
			[
				'class'          => $this->arrayToString( $class, ' ' ),
				'style'          => $this->arrayToString( $style, ';' ),
				'id'             => $this->getId(),
				'data-toggle'    => 'popover',
				'title'          => $heading,
				'data-content'   => $parser->recursiveTagParse(
					(string) $parserRequest->getInput(),
					$parserRequest->getFrame()
				),
				'data-placement' => $this->extractAttribute( 'placement', $attributes ),
				'data-trigger'   => $this->extractAttribute( 'trigger', $attributes ),
			],
			$text
		);
	}

	/**
	 * Calculates the class attribute value from the passed attributes
	 *
	 * @param array $attributes
	 *
	 * @throws MWException cascading {@see \BootstrapComponents\Component::extractAttribute}
	 * @return array
	 */
	private function calculateClassFrom( array $attributes ) {
		if ( !isset( $attributes['color'] ) || !$this->getAttributeManager()->verifyValueFor( 'color', $attributes['color'] ) ) {
			$attributes['color'] = 'info';
		}
		$class = [ 'btn', 'btn-' . $this->extractAttribute( 'color', $attributes, 'info' ) ];
		if ( $size = $this->extractAttribute( 'size', $attributes ) ) {
			$class[] = 'btn-' . $size;
		}
		return $class;
	}
}