<?php
/**
 * @license GNU GPL v3+
 * @since   1.0
 *
 * @author  Tobias Oetterer < oetterer@uni-paderborn.de >
 */

namespace BootstrapComponents;

/**
 * Interface Nestable
 *
 * All entities, that can be handled by the {@see \BootstrapComponents\NestingController}
 *
 * @package BootstrapComponents
 */
interface Nestable {
	/**
	 * Returns the name of the component.
	 *
	 * @return string
	 */
	public function getComponentName();

	/**
	 * Returns the id used in html output. Unique for a page.
	 *
	 * @return string
	 */
	public function getId();
}