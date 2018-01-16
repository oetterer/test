<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents;

use \Html;
use \RequestContext;
use \Title;

/**
 * Class ParserOutputHelper
 *
 * Performs all the adaptions on the ParserOutput
 *
 * @package BootstrapComponents
 */
class ParserOutputHelper {
	/**
	 * To make sure, we only add the tracking category once
	 *
	 * @var bool
	 */
	private $articleTracked;

	/**
	 * To make sure, we only add the error tracking category once
	 *
	 * @var bool
	 */
	private $articleTrackedOnError;

	/**
	 * Holds the name of the skin we use (or false, if there is no skin)
	 *
	 * @var string
	 */
	private $nameOfActiveSkin;

	/**
	 * @var \Parser
	 */
	private $parser;

	/**
	 * ParserOutputHelper constructor.
	 *
	 * @param \Parser $parser
	 */
	public function __construct( $parser ) {
		$this->articleTracked = false;
		$this->articleTrackedOnError = false;
		$this->parser = $parser;
		$this->nameOfActiveSkin = $this->detectSkinInUse();
	}

	/**
	 * Adds the error tracking category to the current page if not done already.
	 */
	public function addErrorTrackingCategory() {
		if ( $this->articleTrackedOnError ) {
			return;
		}
		$this->placeTrackingCategory( 'bootstrap-components-error-tracking-category' );
		$this->articleTrackedOnError = true;
	}

	/**
	 * Adds the supplied modules to the parser output
	 *
	 * @param array $modulesToAdd
	 */
	public function addModules( $modulesToAdd ) {
		$this->parser->getOutput()->addModules( $modulesToAdd );
	}

	/**
	 * Adds the tracking category to the current page if not done already.
	 */
	public function addTrackingCategory() {
		if ( $this->articleTracked ) {
			return;
		}
		$this->placeTrackingCategory( 'bootstrap-components-tracking-category' );
		$this->articleTracked = true;
	}

	/**
	 * @return string
	 */
	public function getNameOfActiveSkin() {
		return $this->nameOfActiveSkin;
	}

	/**
	 * Adds the bootstrap modules and styles to the page, if not done already
	 */
	public function loadBootstrapModules() {
		$this->parser->getOutput()->addModuleStyles( 'ext.bootstrap.styles' );
		$this->parser->getOutput()->addModuleScripts( 'ext.bootstrap.scripts' );
		if ( $this->vectorSkinInUse() ) {
			$this->parser->getOutput()->addModules( 'ext.bootstrapComponents.vector-fix' );
		}
	}

	/**
	 * Formats a text as error text to be added to the output
	 *
	 * @param string $errorMessageName
	 *
	 * @return string
	 */
	public function renderErrorMessage( $errorMessageName ) {
		if ( !$errorMessageName || !trim( $errorMessageName ) ) {
			return '';
		}
		$this->addErrorTrackingCategory();
		return Html::rawElement(
			'span',
			[ 'class' => 'error' ],
			wfMessage( trim( $errorMessageName ) )->inContentLanguage()->parse()
		);
	}

	/**
	 * Returns true, if active skin is vector
	 *
	 * @return bool
	 */
	public function vectorSkinInUse() {
		return strtolower( $this->getNameOfActiveSkin() ) == 'vector';
	}

	/**
	 * @return string
	 */
	private function detectSkinInUse() {
		$skin = RequestContext::getMain()->getSkin();
		return ($skin && is_a( $skin, 'Skin' ) ? $skin->getSkinName() : 'unknown');
	}

	/**
	 * Adds current page to the indicated tracking category, if not done already
	 *
	 * @param String $trackingCategoryMessageName name of the message, containing the tracking category
	 */
	private function placeTrackingCategory( $trackingCategoryMessageName ) {
		$categoryMessage = wfMessage( $trackingCategoryMessageName )->inContentLanguage();
		if ( !$categoryMessage->isDisabled() ) {
			$output = $this->parser->getOutput();
			$cat = Title::makeTitleSafe( NS_CATEGORY, $categoryMessage->text() );
			if ( $cat ) {
				$sort = (string) $output->getProperty( 'defaultsort' );
				$output->addCategory( $cat->getDBkey(), $sort );
			} else {
				wfDebug( __METHOD__ . ": [[MediaWiki:{$trackingCategoryMessageName}]] is not a valid title!\n" );
			}
		}
	}
}