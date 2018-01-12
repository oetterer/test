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
use \Title;

/**
 * Class Button
 *
 * Class for component 'button'
 *
 * @package BootstrapComponents
 */
class Button extends Component {
	/**
	 * @var array
	 */
	private $rawAttributes = [];

	/**
	 * Allows spawning objects (like {@see \BootstrapComponents\Collapse}) to insert additional data inside the button tag.
	 *
	 * @param array $rawAttributes of form attribute => value
	 */
	public function injectRawAttributes( array $rawAttributes ) {
		if ( is_array( $rawAttributes ) && count( $rawAttributes ) ) {
			$this->rawAttributes += $rawAttributes;
		}
	}

	/**
	 * @inheritdoc
	 *
	 * @param ParserRequest $parserRequest
	 */
	public function placeMe( $parserRequest ) {
		if ( !$parserRequest->getInput() ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-button-target-missing' );
		}

		list( $target, $text ) = $this->getTargetAndText( $parserRequest );

		if ( !$target ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-button-target-invalid' );
		}

		list ( $class, $style ) = $this->processCss(
			$this->calculateClassFrom( $parserRequest->getAttributes() ),
			[],
			$parserRequest->getAttributes()
		);

		return [
			Html::rawElement(
				'a',
				[
					'class' => $this->arrayToString( $class, ' ' ),
					'style' => $this->arrayToString( $style, ';' ),
					'role'  => 'button',
					'id'    => $this->getId(),
					'href'  => $target,
				] + $this->rawAttributes,
				$text
			),
			'isHTML'  => true,
			'noparse' => true,
		];
	}

	/**
	 * Calculates the css class string from the attributes array.
	 *
	 * @param string[] $attributes
	 *
	 * @throws MWException cascading {@see \BootstrapComponents\Component::extractAttribute}
	 * @return array
	 */
	private function calculateClassFrom( $attributes ) {

		$class = [ "btn" ];
		$class[] = 'btn-' . $this->extractAttribute( 'color', $attributes, 'default' );
		if ( $size = $this->extractAttribute( 'size', $attributes ) ) {
			$class[] = "btn-" . $size;
		}
		if ( $this->extractAttribute( 'active', $attributes ) !== null ) {
			$class[] = 'active';
		}
		if ( $this->extractAttribute( 'disabled', $attributes ) !== null ) {
			$class[] = 'disabled';
		}
		return $class;
	}

	/**
	 * Generates a valid target a suitable text for the button
	 *
	 * @param ParserRequest $parserRequest
	 *
	 * @return array containing (string|null) target, (string) text. Note that target is null when invalid
	 */
	private function getTargetAndText( ParserRequest $parserRequest ) {
		$parser = $parserRequest->getParser();
		$input = $parserRequest->getInput();
		$attributes = $parserRequest->getAttributes();
		$target = trim( $parser->recursiveTagParse( trim( $input ) ) );
		$text = (isset( $attributes['text'] ) && strlen( $attributes['text'] ))
			? $parser->recursiveTagParse( $attributes['text'] )
			: $target;
		if ( strlen( $target ) && !preg_match( '/^#[A-Za-z_0-9-]+/', $target ) ) {
			// $target is not a fragment (e.g. not #anchor)
			$targetTitle = Title::newFromText( $target );
			$target = $targetTitle ? $targetTitle->getLocalURL() : null;
		}
		return [ $target, $text ];
	}
}