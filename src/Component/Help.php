<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents\Component;

use BootstrapComponents\ApplicationFactory;
use BootstrapComponents\Component;
use BootstrapComponents\ParserRequest;

/**
 * Class Help
 *
 * Class for component 'help'
 *
 * @package BootstrapComponents
 */
class Help extends Component {
	/**
	 * @inheritdoc
	 *
	 * @param ParserRequest $parserRequest
	 */
	public function placeMe( $parserRequest ) {
		$attributes = [
			"heading" => wfMessage( 'bootstrap-components-general-help-heading' )->inContentLanguage()->parse(),
			"footer"  => wfMessage( 'bootstrap-components-general-help-more' )->inContentLanguage()->parse(),
			"color"   => "info",
		];
		$panelParserRequest = ApplicationFactory::getInstance()->getNewParserRequest(
			[ $this->buildTextForGeneralHelp(), $attributes, $parserRequest->getParser(), $parserRequest->getFrame() ]
		);

		$panel = new Panel( $this->getComponentLibrary(), $this->getParserOutputHelper(), $this->getNestingController() );
		return $panel->parseComponent( $panelParserRequest );
	}

	/**
	 * Generates a general help text, that can be printed on a page.
	 *
	 * @throws \MWException cascading {@see \BootstrapComponents\ComponentLibrary::getHandlerTypeFor} and
	 *  {@see \BootstrapComponents\ComponentLibrary::getDescriptionFor}
	 * @return string
	 */
	private function buildTextForGeneralHelp() {
		$registeredComponents = $this->getComponentLibrary()->getRegisteredComponents();
		sort( $registeredComponents );

		$text = "<p>" . wfMessage( 'bootstrap-components-general-help-intro' )->inContentLanguage()->parse() . "</p>\n";

		foreach ( $registeredComponents as $componentName ) {
			$call = $this->getComponentLibrary()->isParserFunction( $componentName )
				? '{{#bootstrap_' . $componentName . ':..}}'
				: '<bootstrap_' . $componentName . ' ..>..</bootstrap_' . $componentName . '>';
			$text .= ";" . $componentName . "\n";
			$text .= ":<code><nowiki>" . $call . "</nowiki></code>\n";
			$text .= "<dd>" . $this->getComponentLibrary()->getDescriptionFor( $componentName ) . "</dd>\n";
		}
		return $text;
	}
}