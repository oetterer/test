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
 * Class Icon
 *
 * Class for component 'icon'
 *
 * @package BootstrapComponents
 */
class Icon extends Component {
	/**
	 * @inheritdoc
	 *
	 * @param ParserRequest $parserRequest
	 */
	public function placeMe( $parserRequest ) {
		if ( !$parserRequest->getInput() ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-glyph-icon-name-missing' );
		}

		return Html::rawElement(
			'span',
			[ 'class' => 'glyphicon glyphicon-' . trim( $parserRequest->getInput() ) ]
		);
	}
}