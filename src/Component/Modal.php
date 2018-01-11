<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents\Component;

use BootstrapComponents\Component;
use BootstrapComponents\ModalBase;
use BootstrapComponents\ParserRequest;
use \Html;
use \MWException;

/**
 * Class Modal
 *
 * Class for component 'modal'
 *
 * @package BootstrapComponents
 */
class Modal extends Component {
	/**
	 * @inheritdoc
	 *
	 * @param ParserRequest $parserRequest
	 */
	public function placeMe( $parserRequest ) {
		$attributes = $parserRequest->getAttributes();
		$parser = $parserRequest->getParser();

		list ( $outerClass, $style ) = $this->processCss( [], [], $attributes );

		$modal = new ModalBase(
			$this->getId(),
			$this->generateTrigger( $parserRequest ),
			$parser->recursiveTagParse(
				$parserRequest->getInput(),
				$parserRequest->getFrame()
			)
		);
		$modal->setOuterClass(
			$this->arrayToString( $outerClass, ' ' )
		);
		$modal->setOuterStyle(
			$this->arrayToString( $style, ';' )
		);
		$modal->setDialogClass(
			$this->calculateInnerClassFrom( $attributes )
		);
		if ( isset( $attributes['heading'] ) && strlen( $attributes['heading'] ) ) {
			$modal->setHeader(
				$parser->recursiveTagParse( $attributes['heading'] )
			);
		}
		if ( isset( $attributes['footer'] ) && strlen( $attributes['footer'] ) ) {
			$modal->setFooter(
				$parser->recursiveTagParse( $attributes['footer'] )
			);
		}
		return $modal->parse();
	}

	/**
	 * Calculates the css class string from the attributes array for the "inner" section (div around body and heading)
	 *
	 * @param string[] $attributes
	 *
	 * @throws MWException cascading {@see \BootstrapComponents\Component::extractAttribute}
	 * @return false|string
	 */
	private function calculateInnerClassFrom( $attributes ) {

		$class = [];

		if ( $size = $this->extractAttribute( 'size', $attributes ) ) {
			$class[] = 'modal-' . $size;
		}
		return $this->arrayToString( $class, ' ' );
	}

	/**
	 * Spawns the button for the modal trigger
	 *
	 * @param ParserRequest $parserRequest
	 *
	 * @throws MWException cascading {@see \BootstrapComponents\Component::extractAttribute}
	 * @return string
	 */
	private function generateButton( $parserRequest ) {
		$attributes = $parserRequest->getAttributes();
		$parser = $parserRequest->getParser();

		return Html::rawElement(
			'button',
			[
				'type'        => 'button',
				'class'       => 'modal-trigger btn btn-' . $this->extractAttribute( 'color', $attributes, 'default' ),
				'data-toggle' => 'modal',
				'data-target' => '#' . $this->getId(),
			],
			$parser->recursiveTagParse(
				$attributes['text'],
				$parserRequest->getFrame()
			)
		);
	}

	/**
	 * @param ParserRequest $parserRequest
	 *
	 * @throws MWException cascading {@see \BootstrapComponents\Modal::generateButton}
	 * @return string
	 */
	private function generateTrigger( $parserRequest ) {
		$attributes = $parserRequest->getAttributes();
		if ( !isset( $attributes['text'] ) || !strlen( trim( $attributes['text'] ) ) ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-modal-text-missing' );
		}
		$input = $parserRequest->getParser()->recursiveTagParse(
			$attributes['text'],
			$parserRequest->getFrame()
		);
		if ( !preg_match( '~^(.*)<a.*href=".+".*>(.+)</a>(.*)$~', $input, $matches )
			&& !preg_match( '~(^.*<img.*src=".+".*>.*)$~', $input, $matches )
		) {
			return $this->generateButton( $parserRequest );
		}
		array_shift( $matches );
		return Html::rawElement(
			'span',
			[
				'class'       => 'modal-trigger',
				'data-toggle' => 'modal',
				'data-target' => '#' . $this->getId(),
			],
			implode( '', $matches )
		);
	}
}