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
 * Class Jumbotron
 *
 * Class for component 'jumbotron'
 *
 * @package BootstrapComponents
 */
class Jumbotron extends Component {
	/**
	 * @inheritdoc
	 *
	 * @param ParserRequest $parserRequest
	 */
	public function placeMe( $parserRequest ) {
		list ( $class, $style ) = $this->processCss( 'jumbotron', [], $parserRequest->getAttributes() );
		# @hack: the outer container is a workaround, to get all the necessary css if not inside a grid container
		# @fixme: used inside mw content, the width calculation for smaller screens is broken (as of Bootstrap 1.2.3)
		return Html::rawElement(
			'div',
			[
				'class' => 'container',
			],
			Html::rawElement(
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
			)
		);
	}
}