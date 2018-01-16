<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents;

use \ConfigException;
use \MediaWiki\MediaWikiServices;
use \MWException;

/**
 * Class NestingController
 *
 * Takes care of some things that occur when nesting bootstrap components
 *
 * @package BootstrapComponents
 */
class NestingController {

	/**
	 * List of ids already in use in the context of the bootstrap components.
	 * Key is of this array is the component name, value is the next usable id
	 *
	 * @var array
	 */
	private $autoincrementPerComponent = [];

	/**
	 * Holds information about the bootstrap component stack,
	 * so that components can be called within components.
	 * Consists of elements of type {@see Nestable}
	 *
	 * @var array
	 */
	private $componentStack;

	/**
	 * When in testing mode, unique ids tend to make things very difficult. So this known, when not to generate them.
	 * @var bool
	 */
	private $disableUniqueIds;

	/**
	 * NestingController constructor.
	 */
	public function __construct() {
		$this->autoincrementPerComponent = [];
		$this->componentStack = [];
		$this->disableUniqueIds =  false;

		try {
			$myConfig = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'BootstrapComponents' );
			$this->disableUniqueIds = $myConfig->has( 'BootstrapComponentsDisableIdsForTestsEnvironment' )
				&& $myConfig->get( 'BootstrapComponentsDisableIdsForTestsEnvironment' );
		} catch ( ConfigException $c ) {
			# nothing
		}
	}

	/**
	 * Signals the closing of a bootstrap component
	 *
	 * @param string|false $id if of the current object we are trying to close
	 *
	 * @throws MWException if current and closing component is different
	 */
	public function close( $id ) {
		$current = $this->getCurrentElement();
		if ( !$current ) {
			throw new MWException( 'Nesting error. Tried to close an empty stack.' );
		}
		if ( $id === false || ($current->getId() != $id) ) {
			throw new MWException( 'Nesting error. Trying to close a component that is not the currently open one.' );
		}
		array_pop( $this->componentStack );
	}

	/**
	 * Generates an id not in use within any bootstrap component yet.
	 *
	 * @param string $componentName
	 *
	 * @return string
	 */
	public function generateUniqueId( $componentName ) {
		if ( $this->disableUniqueIds ) {
			return 'bsc_' . $componentName . '_parserTest';
		}
		if ( !isset( $this->autoincrementPerComponent[$componentName] ) ) {
			$this->autoincrementPerComponent[$componentName] = 0;
		}
		return 'bsc_' . $componentName . '_' . ($this->autoincrementPerComponent[$componentName]++);
	}

	/**
	 * Returns a reference to the last opened component
	 *
	 * @return false|Nestable
	 */
	public function getCurrentElement() {
		return end( $this->componentStack );
	}

	/**
	 * Returns the size of the stack.
	 *
	 * @return int
	 */
	public function getStackSize() {
		return count( $this->componentStack );
	}

	/**
	 * Signals the opening of a bootstrap component (thus letting the nc put the nestable component on its stack)
	 *
	 * @param Nestable $component
	 *
	 * @throws MWException when open is called with an invalid object
	 */
	public function open( &$component ) {
		if ( !$component instanceof Nestable ) {
			throw new MWException( 'Nesting error. Trying to put an object other than a Component an the nesting stack.' );
		}
		array_push( $this->componentStack, $component );
	}
}