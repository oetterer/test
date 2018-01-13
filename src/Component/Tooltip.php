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
 * Class Tooltip
 *
 * Class for component 'tooltip'
 *
 * @package BootstrapComponents
 */
class Tooltip extends Component {
	/**
	 * @inheritdoc
	 *
	 * @param ParserRequest $parserRequest
	 */
	public function placeMe( $parserRequest ) {
		if ( !$parserRequest->getInput() ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-tooltip-content-missing' );
		}
		$tooltip = $this->extractAttribute( 'text', $parserRequest->getAttributes(), false );
		if ( $tooltip === false ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-tooltip-text-missing' );
		}
		list ( $class, $style ) = $this->processCss( [], [], $parserRequest->getAttributes() );

		return Html::rawElement(
			'span',
			[
				'class'          => $this->arrayToString( $class, ' ' ),
				'style'          => $this->arrayToString( $style, ';' ),
				'id'             => $this->getId(),
				'data-toggle'    => 'tooltip',
				'title'          => htmlentities( $parserRequest->getParser()->recursiveTagParse( $tooltip ) ),
				'data-placement' => $this->extractAttribute( 'placement', $parserRequest->getAttributes() ),
			],
			$parserRequest->getParser()->recursiveTagParse(
				$parserRequest->getInput()
			)
		);
	}
}