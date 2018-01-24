<?php
/**
 * Contains the class augmenting the parser output.
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

namespace BootstrapComponents;

use \Html;
use \ParserOutput;
use \RequestContext;
use \Title;

/**
 * Class ParserOutputHelper
 *
 * Performs all the adaptions on the ParserOutput
 *
 * @since 1.0
 */
class ParserOutputHelper {
	/**
	 * To make sure, we only add the tracking category once.
	 *
	 * @var bool $articleTracked
	 */
	private $articleTracked;

	/**
	 * To make sure, we only add the error tracking category once.
	 *
	 * @var bool $articleTrackedOnError
	 */
	private $articleTrackedOnError;

	/**
	 * Holds the name of the skin we use (or false, if there is no skin).
	 *
	 * @var string $nameOfActiveSkin
	 */
	private $nameOfActiveSkin;

	/**
	 * @var \Parser $parser
	 */
	private $parser;

	/**
	 * ParserOutputHelper constructor.
	 *
	 * Do not instantiate directly, but use {@see ApplicationFactory::getParserOutputHelper} instead.
	 *
	 * @param \Parser $parser
	 *
	 * @see ApplicationFactory::getParserOutputHelper
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
	 * Adds the supplied modules to the parser output.
	 *
	 * @param array $modulesToAdd
	 */
	public function addModules( $modulesToAdd ) {
		$parserOutput = $this->parser->getOutput();
		if ( is_a( $parserOutput, ParserOutput::class ) ) {
			// Q: when do we expect \Parser->getOutput() no to be a \ParserOutput? A:During tests.
			$parserOutput->addModules( $modulesToAdd );
		}
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
		$parserOutput = $this->parser->getOutput();
		if ( is_a( $parserOutput, ParserOutput::class ) ) {
			// Q: when do we expect \Parser->getOutput() no to be a \ParserOutput? A:During tests.
			$parserOutput->addModuleStyles( 'ext.bootstrap.styles' );
			$parserOutput->addModuleScripts( 'ext.bootstrap.scripts' );
			if ( $this->vectorSkinInUse() ) {
				$parserOutput->addModules( 'ext.bootstrapComponents.vector-fix' );
			}
		}
	}

	/**
	 * Formats a text as error text so it can be added to the output.
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
			wfMessage( trim( $errorMessageName ) )->inContentLanguage()->title( $this->parser->getTitle() )->parse()
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
	 * Adds current page to the indicated tracking category, if not done already.
	 *
	 * @param String $trackingCategoryMessageName name of the message, containing the tracking category
	 */
	private function placeTrackingCategory( $trackingCategoryMessageName ) {
		$categoryMessage = wfMessage( $trackingCategoryMessageName )->inContentLanguage();
		$parserOutput = $this->parser->getOutput();
		if ( !$categoryMessage->isDisabled() && is_a( $parserOutput, ParserOutput::class ) ) {
			// Q: when do we expect \Parser->getOutput() no to be a \ParserOutput? A:During tests.
			$cat = Title::makeTitleSafe( NS_CATEGORY, $categoryMessage->text() );
			if ( $cat ) {
				$sort = (string) $parserOutput->getProperty( 'defaultsort' );
				$parserOutput->addCategory( $cat->getDBkey(), $sort );
			} else {
				wfDebug( __METHOD__ . ": [[MediaWiki:{$trackingCategoryMessageName}]] is not a valid title!\n" );
			}
		}
	}
}