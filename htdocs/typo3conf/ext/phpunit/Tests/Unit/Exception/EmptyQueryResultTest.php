<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2013 Oliver Klee (typo3-coding@oliverklee.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Phpunit_Exception_EmptyQueryResultTest extends Tx_Phpunit_TestCase {
	/**
	 * the saved content of $GLOBALS['TYPO3_DB']->debugOutput
	 *
	 * @var boolean
	 */
	private $savedDebugOutput;

	/**
	 * the saved content of $GLOBALS['TYPO3_DB']->store_lastBuiltQuery
	 *
	 * @var boolean
	 */
	private $savedStoreLastBuildQuery;

	public function setUp() {
		$this->savedDebugOutput = $GLOBALS['TYPO3_DB']->debugOutput;
		$this->savedStoreLastBuildQuery = $GLOBALS['TYPO3_DB']->store_lastBuiltQuery;

		$GLOBALS['TYPO3_DB']->debugOutput = FALSE;
		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = TRUE;
	}

	public function tearDown() {
		$GLOBALS['TYPO3_DB']->debugOutput = $this->savedDebugOutput;
		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = $this->savedStoreLastBuildQuery;
	}

	/**
	 * @test
	 *
	 * @expectedException Tx_Phpunit_Exception_EmptyQueryResult
	 *
	 * @throws Tx_Phpunit_Exception_EmptyQueryResult
	 */
	public function exceptionCanBeThrown() {
		throw new Tx_Phpunit_Exception_EmptyQueryResult(1334438911);
	}

	/**
	 * @test
	 */
	public function messageAfterQueryWithLastQueryEnabledContainsLastQuery() {
		$GLOBALS['TYPO3_DB']->exec_SELECTquery('title', 'tx_phpunit_test', '');
		$fixture = new Tx_Phpunit_Exception_EmptyQueryResult();

		$this->assertContains(
			'SELECT',
			$fixture->getMessage()
		);
	}
}
?>