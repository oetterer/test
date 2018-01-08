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
 * Class Accordion
 *
 * Class for component 'accordion'
 *
 * @package BootstrapComponents
 */
class Accordion extends Component {
	/**
	 * @inheritdoc
	 *
	 * @param ParserRequest $parserRequest
	 */
	public function placeMe( $parserRequest ) {

		list ( $class, $style ) = $this->processCss( 'panel-group', [], $parserRequest->getAttributes() );

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