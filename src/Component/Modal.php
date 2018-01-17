<?php
/**
 * Contains the component class for rendering a modal.
 *
 * @copyright (C) 2018, Tobias Oetterer, University of Paderborn
 * @license       https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 (or later)
 *
 * This file is part of the MediaWiki extension BootstrapComponents.
 * The BootstrapComponents extension is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The BootstrapComponents extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @file
 * @ingroup       BootstrapComponents
 * @author        Tobias Oetterer
 */

namespace BootstrapComponents\Component;

use BootstrapComponents\AbstractComponent;
use BootstrapComponents\ApplicationFactory;
use BootstrapComponents\ParserRequest;
use \Html;
use \MWException;

/**
 * Class Modal
 *
 * Class for component 'modal'
 *
 * @see   https://github.com/oetterer/BootstrapComponents/blob/master/doc/components.md#Modal
 * @since 1.0
 */
class Modal extends AbstractComponent {
	/**
	 * @inheritdoc
	 *
	 * @param ParserRequest $parserRequest
	 */
	public function placeMe( $parserRequest ) {
		$attributes = $parserRequest->getAttributes();
		$parser = $parserRequest->getParser();

		list ( $outerClass, $style ) = $this->processCss( [], [], $attributes );

		$modal = ApplicationFactory::getInstance()->getModalBuilder(
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