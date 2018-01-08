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
 * Class Badge
 *
 * Class for component 'badge'
 *
 * @package BootstrapComponents
 */
class Badge extends Component {
	/**
	 * @inheritdoc
	 *
	 * @param ParserRequest $parserRequest
	 */
	public function placeMe( $parserRequest ) {
		if ( !$parserRequest->getInput() ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-badge-content-missing' );
		}

		list ( $class, $style ) = $this->processCss( [ 'badge' ], [], $parserRequest->getAttributes() );
		return Html::rawElement(
			'span',
			[
				'class' => $this->arrayToString( $class, ' ' ),
				'style' => $this->arrayToString( $style, ';' ),
				'id'    => $this->getId(),
			],
			$parserRequest->getParser()->recursiveTagParse( $parserRequest->getInput() )
		);
	}
}