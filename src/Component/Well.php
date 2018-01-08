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
 * Class Well
 *
 * Class for component 'well'
 *
 * @package BootstrapComponents
 */
class Well extends Component {
	/**
	 * @inheritdoc
	 *
	 * @param ParserRequest $parserRequest
	 */
	public function placeMe( $parserRequest ) {

		$class = [ 'well' ];
		if ( $size = $this->extractAttribute( 'size', $parserRequest->getAttributes() ) ) {
			$class[] = 'well-' . $size;
		}
		list ( $class, $style ) = $this->processCss( $class, [], $parserRequest->getAttributes() );
		return Html::rawElement(
			'div',
			[
				'class' => $this->arrayToString( $class, ' ' ),
				'style' => $this->arrayToString( $style, ';' ),
				'id'    => $this->getId(),
			],
			$parserRequest->getParser()->recursiveTagParse(
				$parserRequest->getInput(),
				$parserRequest->getFrame()
			)
		);
	}
}