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
 * Class Alert
 *
 * Class for component 'alert'
 *
 * @package BootstrapComponents
 */
class Alert extends Component {
	/**
	 * Indicates, whether this alert is dismissible
	 *
	 * @var boolean
	 */
	private $dismissible;

	/**
	 * @inheritdoc
	 *
	 * @param ParserRequest $parserRequest
	 */
	public function placeMe( $parserRequest ) {
		$attributes = $parserRequest->getAttributes();

		$this->dismissible = isset( $attributes['dismissible'] );

		$class = $this->calculateClassFrom( $attributes );
		$inside = $parserRequest->getParser()->recursiveTagParse(
			$parserRequest->getInput(),
			$parserRequest->getFrame()
		);
		if ( $this->isDismissible() ) {
			$inside = $this->renderDismissButton() . $inside;
		}

		list ( $class, $style ) = $this->processCss( $class, [], $attributes );
		return Html::rawElement(
			'div',
			[
				'class' => $this->arrayToString( $class, ' ' ),
				'style' => $this->arrayToString( $style, ';' ),
				'id'    => $this->getId(),
				'role'  => 'alert',
			],
			$inside
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
	private function calculateClassFrom( $attributes ) {
		$class = [ 'alert' ];
		$class[] = 'alert-' . $this->extractAttribute( 'color', $attributes, 'info' );

		if ( $dismiss = $this->extractAttribute( 'dismissible', $attributes ) ) {
			if ( $dismiss == 'fade' ) {
				$class = array_merge( $class, [ 'fade', 'in' ] );
			} else {
				$class[] = 'alert-dismissible';
			}
		}
		return $class;
	}

	/**
	 * Indicates, whether this alert is dismissible or not
	 *
	 * @return bool
	 */
	private function isDismissible() {
		return $this->dismissible;
	}


	/**
	 * Generates the button, that lets us dismiss this alert
	 *
	 * @return string
	 */
	private function renderDismissButton() {
		return Html::rawElement(
			'div',
			[
				'type'         => 'button',
				'class'        => 'close',
				'data-dismiss' => 'alert',
				'aria-label'   => wfMessage( 'bootstrap-components-close-element' )->inContentLanguage()->parse(),
			],
			Html::rawElement(
				'span',
				[
					'aria-hidden' => 'true',
				],
				'&times;'
			)
		);
	}
}