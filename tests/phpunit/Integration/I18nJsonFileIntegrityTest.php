<?php

namespace BootstrapComponents\Tests\Integration;

use SMW\Tests\Utils\UtilityFactory;

/**
 * @group extension-bootstrap-components
 * @group medium
 *
 * @license GNU GPL v3+
 * @since 1.0
 *
 * @author mwjames
 * @author Tobias Oetterer
 */
class I18nJsonFileIntegrityTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @param string $file
	 *
	 * @dataProvider i18nFileProvider
	 */
	public function testI18NJsonDecodeEncode( $file ) {

		$jsonFileReader = UtilityFactory::getInstance()->newJsonFileReader( $file );

		$this->assertInternalType(
			'integer',
			$jsonFileReader->getModificationTime()
		);

		$this->assertInternalType(
			'array',
			$jsonFileReader->read()
		);
	}

	/**
	 * @return array
	 */
	public function i18nFileProvider() {

		$provider = [];
		$location = $GLOBALS['wgMessagesDirs']['BootstrapComponents'];

		if ( is_array( $location ) ) {
			$location = reset( $location );
		}

		$bulkFileProvider = UtilityFactory::getInstance()->newBulkFileProvider( $location );
		$bulkFileProvider->searchByFileExtension( 'json' );

		foreach ( $bulkFileProvider->getFiles() as $file ) {
			$provider[] = [ $file ];
		}

		return $provider;
	}
}
