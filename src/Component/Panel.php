<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents\Component;

use BootstrapComponents\ComponentLibrary;
use BootstrapComponents\Component;
use BootstrapComponents\NestingController;
use BootstrapComponents\ParserOutputHelper;
use BootstrapComponents\ParserRequest;
use \Html;
use \MWException;
use \Parser;

/**
 * Class Panel
 *
 * Class for component 'panel'
 *
 * @package BootstrapComponents
 */
class Panel extends Component {

	/**
	 * Indicates, whether this panel is collapsible
	 *
	 * @var bool
	 */
	private $collapsible;

	/**
	 * If true, indicates that we are inside an accordion
	 *
	 * @var bool
	 */
	private $insideAccordion;

	/**
	 * Panel constructor.
	 *
	 * @param ComponentLibrary   $componentLibrary
	 * @param ParserOutputHelper $parserOutputHelper
	 * @param NestingController  $nestingController
	 *
	 * @throws MWException
	 */
	public function __construct( $componentLibrary, $parserOutputHelper, $nestingController ) {
		parent::__construct( $componentLibrary, $parserOutputHelper, $nestingController );
		$this->collapsible = false;
		$this->insideAccordion = null;
		$this->insideAccordion = $this->isInsideAccordion();
	}

	/**
	 * @inheritdoc
	 *
	 * @param ParserRequest $parserRequest
	 */
	public function placeMe( $parserRequest ) {
		$attributes = $parserRequest->getAttributes();
		$parser = $parserRequest->getParser();

		$this->collapsible = isset( $attributes['collapsible'] ) || $this->isInsideAccordion();

		if ( $this->isInsideAccordion() && (!isset( $attributes['heading'] ) || !strlen( $attributes['heading'] )) ) {
			$attributes['heading'] = $this->getId();
		}

		$outerClass = $this->calculateOuterClassFrom( $attributes );
		$innerClass = $this->calculateInnerClassFrom( $attributes );

		list ( $outerClass, $style ) = $this->processCss( $outerClass, [], $attributes );

		return Html::rawElement(
			'div',
			[
				'class' => $this->arrayToString( $outerClass, ' ' ),
				'style' => $this->arrayToString( $style, ';' ),
			],
			$this->processAdditionToPanel( 'heading', $attributes, $parser )
			. Html::rawElement(
				'div',
				[
					'id'    => $this->getId(),
					'class' => $this->arrayToString( $innerClass, ' ' ),
				],
				Html::rawElement(
					'div',
					[
						'class' => 'panel-body',
					],
					$parser->recursiveTagParse( $parserRequest->getInput(), $parserRequest->getFrame() )
				)
				. $this->processAdditionToPanel( 'footer', $attributes, $parser )
			)
		);
	}

	/**
	 * Calculates the css class string from the attributes array
	 *
	 * @param array $attributes
	 *
	 * @throws MWException cascading {@see \BootstrapComponents\Component::extractAttribute}
	 * @return string[]
	 */
	private function calculateOuterClassFrom( &$attributes ) {

		$class = [ 'panel' ];
		$class[] = 'panel-' . $this->extractAttribute( 'color', $attributes, 'default' );
		return $class;
	}

	/**
	 * Calculates the css class from the attributes array for the "inner" section (div around body and footer)
	 *
	 * @param array $attributes
	 *
	 * @return bool|array
	 */
	private function calculateInnerClassFrom( $attributes ) {

		$class = false;
		if ( $this->isCollapsible() ) {
			$class = [ 'panel-collapse', 'collapse', 'fade' ];
			if ( isset( $attributes['active'] ) ) {
				$class[] = 'in';
			}
		}
		return $class;
	}

	/**
	 * Returns my data parent string (the one to put in the heading toggle when collapsible
	 *
	 * @return array
	 */
	private function getDataParent() {
		$parent = $this->getParentComponent();
		if ( $parent && $this->isInsideAccordion() && $parent->getId() ) {
			return [ 'data-parent' => '#' . $parent->getId() ];
		}
		return [];
	}

	/**
	 * Indicates, whether this panel is collapsible or not
	 *
	 * @return bool
	 */
	private function isCollapsible() {
		return $this->collapsible;
	}

	/**
	 * Checks, whether this panel is directly inside an accordion
	 *
	 * @return bool
	 */
	private function isInsideAccordion() {
		if ( !is_null( $this->insideAccordion ) ) {
			return $this->insideAccordion;
		}
		$parent = $this->getParentComponent();
		return $this->insideAccordion = ($parent && ($this->getParentComponent()->getComponentName() == 'accordion'));
	}

	/**
	 * Processes the addition heading or footer.
	 *
	 * This examines $attributes and produces an appropriate heading or footing if corresponding data is found.
	 *
	 * @param string $type
	 * @param array  $attributes
	 * @param Parser $parser
	 *
	 * @return string
	 */
	private function processAdditionToPanel( $type, $attributes, Parser $parser ) {
		if ( !isset( $attributes[$type] ) ) {
			return '';
		}
		$inside = $parser->recursiveTagParse( $attributes[$type] );
		$newAttributes = [
			'class' => 'panel-' . $type,
		];
		if ( $type == 'heading' ) {
			if ( $this->isCollapsible() ) {
				$newAttributes += [
						'data-toggle' => 'collapse',
						'href'        => '#' . $this->getId(),
					] + $this->getDataParent();
			}
			$inside = Html::rawElement(
				'h4',
				[
					'class' => 'panel-title',
					'style' => 'margin-top:0;padding-top:0;',
				],
				$inside
			);
		}

		return Html::rawElement(
			'div',
			$newAttributes,
			$inside
		);
	}
}