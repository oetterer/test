<?php
/**
 * Contains the class holding a modal building kit.
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

namespace BootstrapComponents;

use \Html;

/**
 * Class ModalBase
 *
 * This is a low layer class, that helps build a modal. It does not have access to a parser, so it expects all content
 * elements to be hardened by you (with the help of {@see Parser::recursiveTagParse}). All attribute elements
 * will be hardened here, through the use of {@see Html::rawElement}.
 *
 * @since 1.0
 */
class ModalBuilder {

	/**
	 * @var string $content
	 */
	private $content;

	/**
	 * @var string|false $footer
	 */
	private $footer;

	/**
	 * @var string|false $header
	 */
	private $header;

	/**
	 * @var string $id
	 */
	private $id;

	/**
	 * @var string|false $bodyClass
	 */
	private $bodyClass;

	/**
	 * @var string|false $bodyStyle
	 */
	private $bodyStyle;

	/**
	 * @var string|false $dialogClass
	 */
	private $dialogClass;

	/**
	 * @var string|false $dialogStyle
	 */
	private $dialogStyle;

	/**
	 * @var string|false $outerClass
	 */
	private $outerClass;

	/**
	 * @var string|false $outerStyle
	 */
	private $outerStyle;

	/**
	 * @var string $trigger
	 */
	private $trigger;

	/**
	 * With this, you can wrap a generic trigger element inside a span block, that hopefully should
	 * work as a trigger for the modal
	 *
	 * @param string $element
	 * @param string $id
	 *
	 * @return string
	 */
	public static function wrapTriggerElement( $element, $id ) {
		return Html::rawElement(
			'span',
			[
				'class'       => 'modal-trigger',
				'data-toggle' => 'modal',
				'data-target' => '#' . $id,
			],
			$element
		);
	}

	/**
	 * ModalBase constructor.
	 *
	 * Takes $id, $trigger and $content and produces a modal with the html id $id, using $content as the
	 * body content of the opening modal. For trigger, you can use a generic html code and wrap it in
	 * {@see \BootstrapComponents\ModalBase::wrapTriggerElement}, or you make sure you generate
	 * a correct trigger for yourself, using the necessary attributes and especially the id, you supplied
	 * here (see {@see \BootstrapComponents\Component\Modal::generateButton} for example).
	 *
	 * Do not instantiate directly, but use {@see ApplicationFactory::getModalBuilder}
	 * instead.
	 *
	 * @see ApplicationFactory::getModalBuilder
	 * @see \BootstrapComponents\Component\Modal::generateButton
	 *
	 * @param string $id
	 * @param string $trigger must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 * @param string $content must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 */

	public function __construct( $id, $trigger, $content ) {
		$this->id = $id;
		$this->trigger = $trigger;
		$this->content = $content;
	}

	/**
	 * @return string
	 */
	public function parse() {
		return $this->getTrigger()
			. Html::rawElement(
				'div',
				[
					'class'       => $this->compileClass(
						'modal fade',
						$this->getOuterClass()
					),
					'style'       => $this->getOuterStyle(),
					'role'        => 'dialog',
					'id'          => $this->getId(),
					'aria-hidden' => 'true',
				],
				Html::rawElement(
					'div',
					[
						'class' => $this->compileClass(
							'modal-dialog',
							$this->getDialogClass()
						),
						'style' => $this->getDialogStyle(),
					],
					Html::rawElement(
						'div',
						[ 'class' => 'modal-content' ],
						$this->generateHeader(
							$this->getHeader()
						)
						. $this->generateBody(
							$this->getContent()
						)
						. $this->generateFooter(
							$this->getFooter()
						)
					)
				)
			);
	}

	/**
	 * @param string|false $bodyClass
	 *
	 * @return ModalBuilder
	 */
	public function setBodyClass( $bodyClass ) {
		$this->bodyClass = $bodyClass;
		return $this;
	}

	/**
	 * @param string|false $bodyStyle
	 *
	 * @return ModalBuilder
	 */
	public function setBodyStyle( $bodyStyle ) {
		$this->bodyStyle = $bodyStyle;
		return $this;
	}

	/**
	 * @param string|false $dialogClass
	 *
	 * @return ModalBuilder
	 */
	public function setDialogClass( $dialogClass ) {
		$this->dialogClass = $dialogClass;
		return $this;
	}

	/**
	 * @param string|false $dialogStyle
	 *
	 * @return ModalBuilder
	 */
	public function setDialogStyle( $dialogStyle ) {
		$this->dialogStyle = $dialogStyle;
		return $this;
	}

	/**
	 * @param string|false $footer must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 *
	 * @return ModalBuilder
	 */
	public function setFooter( $footer ) {
		$this->footer = $footer;
		return $this;
	}

	/**
	 * @param string|false $header must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 *
	 * @return ModalBuilder
	 */
	public function setHeader( $header ) {
		$this->header = $header;
		return $this;
	}

	/**
	 * @param string|false $outerClass
	 *
	 * @return ModalBuilder
	 */
	public function setOuterClass( $outerClass ) {
		$this->outerClass = $outerClass;
		return $this;
	}

	/**
	 * @param string|false $outerStyle
	 *
	 * @return ModalBuilder
	 */
	public function setOuterStyle( $outerStyle ) {
		$this->outerStyle = $outerStyle;
		return $this;
	}

	/**
	 * @param string $baseClass
	 * @param string|false $additionalClass
	 *
	 * @return string
	 */
	protected function compileClass( $baseClass, $additionalClass ) {
		if ( trim( $additionalClass ) ) {
			return $baseClass . ' ' . trim( $additionalClass );
		}
		return $baseClass;
	}

	/**
	 * @return string
	 */
	protected function getContent() {
		return $this->content;
	}

	/**
	 * @return string|false
	 */
	protected function getBodyClass() {
		return $this->bodyClass;
	}

	/**
	 * @return string|false
	 */
	protected function getBodyStyle() {
		return $this->bodyStyle;
	}

	/**
	 * @return string|false
	 */
	protected function getDialogClass() {
		return $this->dialogClass;
	}

	/**
	 * @return string|false
	 */
	protected function getDialogStyle() {
		return $this->dialogStyle;
	}

	/**
	 * @return string|false
	 */
	protected function getFooter() {
		return $this->footer;
	}

	/**
	 * @return string|false
	 */
	protected function getHeader() {
		return $this->header;
	}

	/**
	 * @return string
	 */
	protected function getId() {
		return $this->id;
	}

	/**
	 * @return string|false
	 */
	protected function getOuterClass() {
		return $this->outerClass;
	}

	/**
	 * @return string|false
	 */
	protected function getOuterStyle() {
		return $this->outerStyle;
	}

	/**
	 * @return string
	 */
	protected function getTrigger() {
		if ( preg_match( '/data-toggle[^"]+"modal/', $this->trigger )
			&& preg_match( '/data-target[^"]+"#' . $this->getId() . '"/', $this->trigger )
			&& preg_match( '/class[^"]+"[^"]*modal-trigger' . '/', $this->trigger )
		) {
			return $this->trigger;
		}
		return self::wrapTriggerElement( $this->trigger, $this->getId() );
	}

	/**
	 * Generates the body section
	 *
	 * @param string $content must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 *
	 * @return string
	 */
	private function generateBody( $content ) {
		return Html::rawElement(
				'div',
				[
					'class' => $this->compileClass( 'modal-body', $this->getBodyClass() ),
					'style' => $this->getBodyStyle(),
				],
				$content
			) . "\n";
	}

	/**
	 * Generates the footer section
	 *
	 * @param string|false $footer must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 *
	 * @return string
	 */
	private function generateFooter( $footer = '' ) {
		if ( empty( $footer ) ) {
			$footer = '';
		}
		$close = wfMessage( 'bootstrap-components-close-element' )->inContentLanguage()->parse();
		return Html::rawElement(
				'div',
				[ 'class' => 'modal-footer' ],
				$footer . Html::rawElement(
					'button',
					[
						'type'         => 'button',
						'class'        => 'btn btn-default',
						'data-dismiss' => 'modal',
						'aria-label'   => $close,
					],
					$close
				)
			) . "\n";
	}

	/**
	 * Generates the header section together with the dismiss X and the heading, if provided
	 *
	 * @param string|false $header must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 *
	 * @return string
	 */
	private function generateHeader( $header = '' ) {
		if ( empty( $header ) ) {
			$header = '';
		}
		$button = Html::rawElement(
			'button',
			[
				'type'         => 'button',
				'class'        => 'close',
				'data-dismiss' => 'modal',
				'aria-label'   => wfMessage( 'bootstrap-components-close-element' )->inContentLanguage()->parse(),
			],
			Html::rawElement(
				'span',
				[ 'aria-hidden' => 'true' ],
				'&times;'
			)
		);
		return Html::rawElement(
				'div',
				[ 'class' => 'modal-header' ],
				$button . ($header !== ''
					? Html::rawElement(
						'span',
						[ 'class' => 'modal-title' ],
						$header
					)
					: ''
				)
			) . "\n";
	}
}