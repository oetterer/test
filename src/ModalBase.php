<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents;

use \Html;

/**
 * Class ModalBase
 *
 * This is a low layer class, that helps build a modal. It does no have access to a parser, so it expects all content
 * elements to be hardened by you (with the help of {@see Parser::recursiveTagParse}). All attribute elements
 * will be hardened here, through the use of {@see Html::rawElement}
 *
 * @package BootstrapComponents
 */
class ModalBase {

	/**
	 * @var string
	 */
	private $content;

	/**
	 * @var string
	 */
	private $footer;

	/**
	 * @var string
	 */
	private $header;

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $bodyClass;

	/**
	 * @var string
	 */
	private $bodyStyle;

	/**
	 * @var string
	 */
	private $dialogClass;

	/**
	 * @var string
	 */
	private $dialogStyle;

	/**
	 * @var string
	 */
	private $outerClass;

	/**
	 * @var string
	 */
	private $outerStyle;

	/**
	 * @var string
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
	 * body content of the opening modal. For trigger, you can use a generic html code and warp it in
	 * {@see \BootstrapComponents\ModalBase::wrapTriggerElement}, or you make sure you generate
	 * a correct trigger for yourself, using the necessary attributes and especially the id, you supplied
	 * here (see {@see \BootstrapComponents\Component\Modal::generateButton} for example).
	 *
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
	 * @return ModalBase
	 */
	public function setBodyClass( $bodyClass ) {
		$this->bodyClass = $bodyClass;
		return $this;
	}

	/**
	 * @param string|false $bodyStyle
	 *
	 * @return ModalBase
	 */
	public function setBodyStyle( $bodyStyle ) {
		$this->bodyStyle = $bodyStyle;
		return $this;
	}

	/**
	 * @param string|false $dialogClass
	 *
	 * @return ModalBase
	 */
	public function setDialogClass( $dialogClass ) {
		$this->dialogClass = $dialogClass;
		return $this;
	}

	/**
	 * @param string|false $dialogStyle
	 *
	 * @return ModalBase
	 */
	public function setDialogStyle( $dialogStyle ) {
		$this->dialogStyle = $dialogStyle;
		return $this;
	}

	/**
	 * @param string|false $footer must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 *
	 * @return ModalBase
	 */
	public function setFooter( $footer ) {
		$this->footer = $footer;
		return $this;
	}

	/**
	 * @param string|false $header must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 *
	 * @return ModalBase
	 */
	public function setHeader( $header ) {
		$this->header = $header;
		return $this;
	}

	/**
	 * @param string|false $outerClass
	 *
	 * @return ModalBase
	 */
	public function setOuterClass( $outerClass ) {
		$this->outerClass = $outerClass;
		return $this;
	}

	/**
	 * @param string|false $outerStyle
	 *
	 * @return ModalBase
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
	 * @return string
	 */
	protected function getBodyClass() {
		return $this->bodyClass;
	}

	/**
	 * @return string
	 */
	protected function getBodyStyle() {
		return $this->bodyStyle;
	}

	/**
	 * @return string
	 */
	protected function getDialogClass() {
		return $this->dialogClass;
	}

	/**
	 * @return string
	 */
	protected function getDialogStyle() {
		return $this->dialogStyle;
	}

	/**
	 * @return string
	 */
	protected function getFooter() {
		return $this->footer;
	}

	/**
	 * @return string
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
	 * @return string
	 */
	protected function getOuterClass() {
		return $this->outerClass;
	}

	/**
	 * @return string
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
	 * @param string $footer must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 *
	 * @return string
	 */
	private function generateFooter( $footer = '' ) {
		if ( is_null( $footer ) ) {
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
	 * @param string $header must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 *
	 * @return string
	 */
	private function generateHeader( $header = '' ) {
		if ( is_null( $header ) ) {
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