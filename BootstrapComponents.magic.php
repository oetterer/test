<?php
/**
 * @license GNU GPL v3+
 * @since 1.0
 *
 * @file
 *
 * @author Tobias Oetterer < oetterer@uni-paderborn.de >
 */

$magicWords = [];

$componentLibrary = \BootstrapComponents\ApplicationFactory::getInstance()->getComponentLibrary();

/** English
 * @author Tobias Oetterer < oetterer@uni-paderborn.de >
 */
$magicWords['en'] = $componentLibrary->compileMagicWordsArray();
