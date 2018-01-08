<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents\Component;

use BootstrapComponents\ApplicationFactory;
use BootstrapComponents\Component;
use BootstrapComponents\ParserRequest;
use \Html;
use \MWException;

/**
 * Class Collapse
 *
 * Class for component 'collapse'
 *
 * @package BootstrapComponents
 */
class Collapse extends Component {
	/**
	 * @inheritdoc
	 *
	 * @param ParserRequest $parserRequest
	 */
	public function placeMe( $parserRequest ) {
		$buttonPrintOut = $this->generateButton( clone $parserRequest );

		list ( $class, $style ) = $this->processCss( 'collapse', [], $parserRequest->getAttributes() );
		return $buttonPrintOut . Html::rawElement(
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


	/**
	 * Spawns the button for our collapse component
	 *
	 * @param ParserRequest $parserRequest
	 *
	 * @throws MWException cascading {@see \BootstrapComponents\Component::parseComponent}
	 * @return string
	 */
	private function generateButton( ParserRequest $parserRequest ) {
		$button = new Button( $this->getComponentLibrary(), $this->getParserOutputHelper(), $this->getNestingController() );
		$button->injectRawAttributes( [ 'data-toggle' => 'collapse' ] );

		$buttonAttributes = $parserRequest->getAttributes();
		unset( $buttonAttributes['id'] );
		$buttonParserRequest = ApplicationFactory::getInstance()->getNewParserRequest(
			[ '#' . $this->getId(), $buttonAttributes, $parserRequest->getParser(), $parserRequest->getFrame() ]
		);

		$buttonPrintOut = $button->parseComponent( $buttonParserRequest );
		if ( is_array( $buttonPrintOut ) ) {
			$buttonPrintOut = $buttonPrintOut[0];
		}
		return $buttonPrintOut;
	}
}