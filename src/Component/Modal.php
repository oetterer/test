<?php
/**
 * Contains the component class for rendering a modal.
 *
 * @copyright (C) 2018, Tobias Oetterer, Paderborn University
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
use BootstrapComponents\ModalBuilder;
use BootstrapComponents\ParserRequest;
use \Html;
use \MWException;

/**
 * Class Modal
 *
 * Class for component 'modal'
 *
 * @see   https://github.com/oetterer/BootstrapComponents/blob/master/docs/components.md#Modal
 * @since 1.0
 */
class Modal extends AbstractComponent {
	/**
	 * @inheritdoc
	 *
	 * @param string $input
	 */
	public function placeMe( $input ) {
		list ( $outerClass, $style ) = $this->processCss( [], [] );

		$modal = ApplicationFactory::getInstance()->getModalBuilder(
			$this->getId(),
			$this->generateTrigger(),
			$input
		);
		return $modal->setOuterClass(
			$this->arrayToString( $outerClass, ' ' )
		)->setOuterStyle(
			$this->arrayToString( $style, ';' )
		)->setDialogClass(
			$this->calculateInnerClass()
		)->setHeader(
			(string)$this->getValueFor( 'heading' )
		)->setFooter(
			(string)$this->getValueFor( 'footer' )
		)->parse();
	}

	/**
	 * Calculates the css class string from the attributes array for the "inner" section (div around body and heading).
	 *
	 * @return false|string
	 */
	private function calculateInnerClass() {

		$class = [];

		if ( $size = $this->getValueFor( 'size' ) ) {
			$class[] = 'modal-' . $size;
		}
		return $this->arrayToString( $class, ' ' );
	}

	/**
	 * Spawns the button for the modal trigger.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	private function generateButton( $text ) {
		return Html::rawElement(
			'button',
			[
				'type'        => 'button',
				'class'       => 'modal-trigger btn btn-' . $this->getValueFor( 'color', 'default' ),
				'data-toggle' => 'modal',
				'data-target' => '#' . $this->getId(),
			],
			$text
		);
	}

	/**
	 * Generate the trigger element (button or image).
	 *
	 * @return string
	 */
	private function generateTrigger() {
		$text = (string)$this->getValueFor( 'text' );
		if ( empty( $text ) ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-modal-text-missing' );
		}
		if ( !preg_match( '~^(.*)<a.+href=[^>]+>(.+)</a>(.*)$~', $text, $matches )
			&& !preg_match( '~(^.*<img.*src=.+>.*)$~', $text, $matches )
		) {
			return $this->generateButton( $text );
		}
		array_shift( $matches );
		return ModalBuilder::wrapTriggerElement( implode( '', $matches ), $this->getId() );
	}
}