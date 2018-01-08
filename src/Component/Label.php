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
 * Class Label
 *
 * Class for component 'label'
 *
 * @package BootstrapComponents
 */
class Label extends Component {
	/**
	 * @inheritdoc
	 *
	 * @param ParserRequest $parserRequest
	 */
	public function placeMe( $parserRequest ) {
		if ( !$parserRequest->getInput() ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-label-content-missing' );
		}

		$class = [
			'label', 'label-' . $this->extractAttribute(
				'color',
				$parserRequest->getAttributes(),
				'default'
			),
		];
		list ( $class, $style ) = $this->processCss( $class, [], $parserRequest->getAttributes() );
		return Html::rawElement(
			'span',
			[
				'class' => $this->arrayToString( $class, ' ' ),
				'style' => $this->arrayToString( $style, ';' ),
				'id'    => $this->getId(),
			],
			$parserRequest->getParser()->recursiveTagParse(
				$parserRequest->getInput()
			)
		);
	}
}