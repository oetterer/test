<?php
/**
 * Contains the component class for rendering a popover.
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
use BootstrapComponents\ParserRequest;
use \Html;
use \MWException;

/**
 * Class Popover
 *
 * Class for component 'popover'
 *
 * @see   https://github.com/oetterer/BootstrapComponents/blob/master/doc/components.md#Popover
 * @since 1.0
 */
class Popover extends AbstractComponent {
	/**
	 * @inheritdoc
	 *
	 * @param ParserRequest $parserRequest
	 */
	public function placeMe( $parserRequest ) {
		$attributes = $parserRequest->getAttributes();
		$parser = $parserRequest->getParser();

		if ( isset( $attributes['heading'] ) && strlen( $attributes['heading'] ) ) {
			$heading = $parser->recursiveTagParse( $attributes['heading'] );
		} else {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-popover-heading-missing' );
		}
		if ( isset( $attributes['text'] ) && strlen( $attributes['text'] ) ) {
			$text = $parser->recursiveTagParse( $attributes['text'] );
		} else {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-popover-text-missing' );
		}

		list ( $class, $style ) = $this->processCss(
			$this->calculateClassFrom(),
			[]
		);

		return Html::rawElement(
			'button',
			[
				'class'          => $this->arrayToString( $class, ' ' ),
				'style'          => $this->arrayToString( $style, ';' ),
				'id'             => $this->getId(),
				'data-toggle'    => 'popover',
				'title'          => $heading,
				'data-content'   => $parser->recursiveTagParse(
					(string) $parserRequest->getInput(),
					$parserRequest->getFrame()
				),
				'data-placement' => $this->getValueFor( 'placement' ),
				'data-trigger'   => $this->getValueFor( 'trigger' ),
			],
			$text
		);
	}

	/**
	 * Calculates the class attribute value from the passed attributes
	 *
	 * @return array
	 */
	private function calculateClassFrom() {
		$class = [ 'btn', 'btn-' . $this->getValueFor( 'color', 'info' ) ];
		if ( $size = $this->getValueFor( 'size' ) ) {
			$class[] = 'btn-' . $size;
		}
		return $class;
	}
}