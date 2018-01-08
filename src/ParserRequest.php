<?php

namespace BootstrapComponents;

use \MWException;
use \Parser;
use \PPFrame;

/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */
class ParserRequest {
	/**
	 * @var string[]
	 */
	private $attributes;

	/**
	 * @var string
	 */
	private $input;

	/**
	 * @var PPFrame
	 */
	private $frame;

	/**
	 * @var Parser
	 */
	private $parser;

	/**
	 * ParserRequest constructor.
	 *
	 * @param array  $argumentsPassedByParser
	 * @param string $handlerType
	 *
	 * @throws MWException
	 */
	public function __construct( array $argumentsPassedByParser, $handlerType ) {
		list( $this->input, $this->attributes, $this->parser, $this->frame
			) =
			$this->processArguments( $argumentsPassedByParser, $handlerType );
		if ( !is_array( $this->attributes ) ) {
			$this->attributes = [ $this->attributes ];
		}
		if ( !$this->parser || !$this->parser instanceof Parser ) {
			throw new MWException( 'Invalid parser object passed to component ' . $handlerType . '!' );
		}
	}

	/**
	 * @return string[]
	 */
	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * @return string
	 */
	public function getInput() {
		return $this->input;
	}

	/**
	 * @return PPFrame
	 */
	public function getFrame() {
		return $this->frame;
	}

	/**
	 * @return Parser
	 */
	public function getParser() {
		return $this->parser;
	}

	/**
	 * Converts an array of values in form [0] => "name=value" into a real
	 * associative array in form [name] => value. If no = is provided,
	 * true is assumed like this: [name] => true
	 *
	 * Note: shamelessly copied, see link below
	 *
	 * @link https://www.mediawiki.org/w/index.php?title=Manual:Parser_functions&oldid=2572048
	 *
	 * @param array $options
	 *
	 * @throws MWException
	 * @return array $results
	 */
	private function extractParserFunctionOptions( $options ) {
		if ( !$options ) {
			return [];
		}
		if ( !is_array( $options ) ) {
			$options = [ $options ];
		}
		$results = [];
		foreach ( $options as $option ) {
			if ( !is_string( $option ) ) {
				throw new MWException( 'Arguments passed to bootstrap component are invalid!' );
			}
			$pair = explode( '=', $option, 2 );
			if ( count( $pair ) === 2 ) {
				$name = trim( $pair[0] );
				$value = trim( $pair[1] );
				$results[$name] = $value;
			}

			if ( count( $pair ) === 1 ) {
				$name = trim( $pair[0] );
				if ( strlen( $name ) ) {
					$results[$name] = true;
				}
			}
		}
		return $results;
	}

	/**
	 * Parses the arguments passed to parse() method depending on handler type
	 * (parser function or tag extension).
	 *
	 * @param array  $argumentsPassedByParser
	 * @param string $handlerType
	 *
	 * @throws MWException if argument list does not match handler type.
	 * @return array consisting of (string) $input, (array) $options, (Parser) $parser, and optional (PPFrame) $frame
	 */
	private function processArguments( $argumentsPassedByParser, $handlerType ) {
		if ( $handlerType == ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION ) {
			if ( count( $argumentsPassedByParser ) != 4 ) {
				throw new MWException( 'Argument list passed to bootstrap tag component is invalid!' );
			}
			return $argumentsPassedByParser;
		} else {
			$parser = array_shift( $argumentsPassedByParser );
			$input = isset( $argumentsPassedByParser[0] ) ? $argumentsPassedByParser[0] : '';
			unset( $argumentsPassedByParser[0] );
			$attributes = $this->extractParserFunctionOptions( $argumentsPassedByParser );
			return [ $input, $attributes, $parser, null ];
		}
	}
}