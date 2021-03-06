<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2013 AOE media GmbH <dev@aoemedia.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Database testcase base class.
 *
 * @package TYPO3
 * @subpackage tx_phpunit
 *
 * @author Michael Klapper <michael.klapper@aoemedia.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class Tx_Phpunit_Database_TestCase extends Tx_Phpunit_TestCase {
	/**
	 * name of a test database
	 *
	 * @var string
	 */
	protected $testDatabase = '';

	/**
	 * Constructs a test case with the given name.
	 *
	 * @param string $name the name of a testcase
	 * @param array $data ?
	 * @param string $dataName ?
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '') {
		parent::__construct($name, $data, $dataName);
		$this->testDatabase = strtolower(TYPO3_db . '_test');
	}

	/**
	 * Selects the TYPO3 database (again).
	 *
	 * If you have selected any non-TYPO3 in your unit tests, you need to
	 * call this function in tearDown() in order to avoid problems with the
	 * following unit tests and the TYPO3 back-end.
	 *
	 * @return void
	 */
	protected function switchToTypo3Database() {
		$this->selectDatabase(TYPO3_db, $GLOBALS['TYPO3_DB']);
	}

	/**
	 * Accesses the TYPO3 database instance and uses it to fetch the list of
	 * available databases. Then this function creates a test database (if none
	 * has been set up yet).
	 *
	 * @return boolean
	 *         TRUE if the database has been created successfully (or if there
	 *         already is a test database), FALSE otherwise
	 */
	protected function createDatabase() {
		$success = TRUE;

		$this->dropDatabase();
		/** @var $db t3lib_DB */
		$db = $GLOBALS['TYPO3_DB'];
		$databaseNames = $db->admin_get_dbs();

		if (!in_array($this->testDatabase, $databaseNames)) {
			if ($db->admin_query('CREATE DATABASE `' . $this->testDatabase . '`') === FALSE) {
				$success = FALSE;
			}
		}

		return $success;
	}

	/**
	 * Drops all tables in the test database.
	 *
	 * @return void
	 */
	protected function cleanDatabase() {
		/** @var $db t3lib_DB */
		$db = $GLOBALS['TYPO3_DB'];
		if (!in_array($this->testDatabase, $db->admin_get_dbs())) {
			return;
		}

		$this->selectDatabase($this->testDatabase, $db);

		$tables = $this->getDatabaseTables();
		foreach ($tables as $tableName) {
			$db->admin_query('DROP TABLE `' . $tableName . '`');
		}
	}

	/**
	 * Drops the test database.
	 *
	 * @return boolean
	 *         TRUE if the database has been dropped successfully, FALSE otherwise
	 */
	protected function dropDatabase() {
		/** @var $db t3lib_DB */
		$db = $GLOBALS['TYPO3_DB'];
		if (!in_array($this->testDatabase, $db->admin_get_dbs())) {
			return TRUE;
		}

		$this->selectDatabase($this->testDatabase, $db);

		return ($db->admin_query('DROP DATABASE `' .  $this->testDatabase . '`' ) !== FALSE);
	}

	/**
	 * Sets the TYPO3 database instance to a test database.
	 *
	 * Note: This function does not back up the currenty TYPO3 database instance.
	 *
	 * @param string $databaseName
	 *        the name of the test database to use; if none is provided, the
	 *        name of the current TYPO3 database plus a suffix "_test" is used
	 *
	 * @return t3lib_DB the test database
	 */
	protected function useTestDatabase($databaseName = NULL) {
		/** @var $db t3lib_DB */
		$db = $GLOBALS['TYPO3_DB'];

		if ($this->selectDatabase($databaseName ? $databaseName : $this->testDatabase, $db) !== TRUE) {
			$this->markTestSkipped('This test is skipped because the test database is not available.');
		}

		return $db;
	}

	/**
	 * Selects the database depending on TYPO3 version.
	 *
	 * @param string $databaseName the name of the database to select
	 * @param t3lib_DB $database database object to process the change
	 *
	 * @return boolean
	 */
	protected function selectDatabase($databaseName, t3lib_DB $database) {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) < 6001000) {
			$result = $database->sql_select_db($databaseName);
		} else {
			$database->setDatabaseName($databaseName);
			$result = $database->sql_select_db();
		}

		return $result;
	}

	/**
	 * Imports the ext_tables.sql statements from the given extensions.
	 *
	 * @param array $extensions
	 *        keys of the extensions to import, may be empty
	 * @param boolean $importDependencies
	 *        whether to import dependency extensions on which the given extensions
	 *        depend as well
	 * @param array &$skipDependencies
	 *        keys of the extensions to skip, may be empty, will be modified
	 *
	 * @return void
	 */
	protected function importExtensions(
		array $extensions, $importDependencies = FALSE, array &$skipDependencies = array()
	) {
		$this->useTestDatabase();

		foreach ($extensions as $extensionName) {
			if (!t3lib_extMgm::isLoaded($extensionName)) {
				$this->markTestSkipped(
					'This test is skipped because the extension ' . $extensionName .
						' which was marked for import is not loaded on your system!'
				);
			} elseif (in_array($extensionName, $skipDependencies)) {
				continue;
			}

			$skipDependencies = array_merge($skipDependencies, array($extensionName));

			if ($importDependencies) {
				$dependencies = $this->findDependencies($extensionName);
				if (is_array($dependencies)) {
					$this->importExtensions($dependencies, TRUE, $skipDependencies);
				}
			}

			$this->importExtension($extensionName);
		}

		// TODO: The hook should be replaced by real clean up and rebuild the whole
		// "TYPO3_CONF_VARS" in order to have a clean testing environment.
		// hook to load additional files
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['importExtensions_additionalDatabaseFiles'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['phpunit']['importExtensions_additionalDatabaseFiles'] as $file) {
				$sqlFilename = t3lib_div::getFileAbsFileName($file);
				$fileContent = t3lib_div::getUrl($sqlFilename);

				$this->importDatabaseDefinitions($fileContent);
			}
		}
	}

	/**
	 * Gets the names of all tables in the database with the given name.
	 *
	 * @param string $databaseName
	 *        the name of the database from which to retrieve the table names,
	 *        if none is provided, the name of the current TYPO3 database plus a
	 *        suffix "_test" is used
	 *
	 * @return array<string>
	 *        the names of all tables in the database $databaseName, might be empty
	 */
	protected function getDatabaseTables($databaseName = NULL) {
		$db = $this->useTestDatabase($databaseName);

		$tableNames = array();

		$res = $db->sql_query('show tables');
		while (($row = $db->sql_fetch_row($res))) {
			$tableNames[] = $row[0];
		}

		return $tableNames;
	}

	/**
	 * Imports the ext_tables.sql file of the extension with the given name
	 * into the test database.
	 *
	 * @param string $extensionName
	 *        the name of the installed extension to import, must not be empty
	 *
	 * @return void
	 */
	private function importExtension($extensionName) {
		$sqlFilename = t3lib_div::getFileAbsFileName(t3lib_extMgm::extPath($extensionName) . 'ext_tables.sql');
		$fileContent = t3lib_div::getUrl($sqlFilename);

		$this->importDatabaseDefinitions($fileContent);
	}

	/**
	 * Imports the data from the stddb tables.sql file.
	 *
	 * Example/intended usage:
	 *
	 * <pre>
	 * public function setUp() {
	 *   $this->createDatabase();
	 *   $db = $this->useTestDatabase();
	 *   $this->importStdDB();
	 *   $this->importExtensions(array('cms', 'static_info_tables', 'templavoila'));
	 * }
	 * </pre>
	 *
	 * @return void
	 */
	protected function importStdDb() {
		$sqlFilename = t3lib_div::getFileAbsFileName(PATH_t3lib . 'stddb/tables.sql');
		$fileContent = t3lib_div::getUrl($sqlFilename);

		$this->importDatabaseDefinitions($fileContent);

		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4006000) {
			// make sure missing caching framework tables do not get into the way
			$cacheTables = t3lib_cache::getDatabaseTableDefinitions();
			$this->importDatabaseDefinitions($cacheTables);
		}
	}

	/**
	 * Imports the SQL definitions from a (ext_)tables.sql file.
	 *
	 * @param string $definitionContent
	 *        the SQL to import, must not be empty
	 *
	 * @return void
	 */
	private function importDatabaseDefinitions($definitionContent) {
		if (t3lib_utility_VersionNumber::convertVersionNumberToInteger(TYPO3_version) >= 4006000) {
			/* @var $install t3lib_install_Sql */
			$install = t3lib_div::makeInstance('t3lib_install_Sql');
		} else {
			/* @var $install t3lib_install */
			$install = t3lib_div::makeInstance('t3lib_install');
		}

		$fieldDefinitionsFile = $install->getFieldDefinitions_fileContent($definitionContent);
		if (empty($fieldDefinitionsFile)) {
			return;
		}

		// find statements to query
		$fieldDefinitionsDatabase = $install->getFieldDefinitions_fileContent($this->getTestDatabaseSchema());
		$diff = $install->getDatabaseExtra($fieldDefinitionsFile, $fieldDefinitionsDatabase);
		$updateStatements = $install->getUpdateSuggestions($diff);

		$updateTypes = array('add', 'change', 'create_table');

		foreach ($updateTypes as $updateType) {
			if (array_key_exists($updateType, $updateStatements)) {
				foreach ((array) $updateStatements[$updateType] as $string) {
					$GLOBALS['TYPO3_DB']->admin_query($string);
				}
			}
		}
	}

	/**
	 * Returns an SQL dump of the test database.
	 *
	 * @return string SQL dump of the test databse, might be empty
	 */
	private function getTestDatabaseSchema() {
		$db = $this->useTestDatabase();
		$tables = $this->getDatabaseTables();

		// finds create statement for every table
		$linefeed = chr(10);

		$schema = '';
		$db->sql_query('SET SQL_QUOTE_SHOW_CREATE = 0');
		foreach ($tables as $tableName) {
			$res = $db->sql_query('show create table `' . $tableName . '`');
			$row = $db->sql_fetch_row($res);

			// modifies statement to be accepted by TYPO3
			$createStatement = preg_replace('/ENGINE.*$/', '', $row[1]);
			$createStatement = preg_replace(
				'/(CREATE TABLE.*\()/', $linefeed . '\\1' . $linefeed, $createStatement
			);
			$createStatement = preg_replace('/\) $/', $linefeed . ')', $createStatement);

			$schema .= $createStatement . ';';
		}

		return $schema;
	}

	/**
	 * Finds all direct dependencies of the extension with the key $extKey.
	 *
	 * @param string $extKey the key of an installed extension, must not be empty
	 *
	 * @return array<string>|NULL
	 *         the keys of all extensions on which the given extension depends,
	 *         will be NULL if the dependencies could not be determined
	 */
	private function findDependencies($extKey) {
		$path = t3lib_div::getFileAbsFileName(t3lib_extMgm::extPath($extKey) . 'ext_emconf.php');
		$_EXTKEY = $extKey;
		// This include is allowed. This is an exception in the TYPO3CMS standard.
		include($path);

		$dependencies = $EM_CONF[$_EXTKEY]['constraints']['depends'];
		if (!is_array($dependencies)) {
			return NULL;
		}

		// remove php and typo3 extension (not real extensions)
		if (isset($dependencies['php'])) {
			unset($dependencies['php']);
		}
		if (isset($dependencies['typo3'])) {
			unset($dependencies['typo3']);
		}

		return array_keys($dependencies);
	}

	/**
	 * Imports a data set into the test database,
	 *
	 * @param string $path
	 *        the absolute path to the XML file containing the data set to load
	 *
	 * @return void
	 */
	protected function importDataSet($path) {
		$xml = simplexml_load_file($path);
		$db = $this->useTestDatabase();
		$foreignKeys = array();

		/** @var $table SimpleXMLElement */
		foreach ($xml->children() as $table) {
			$insertArray = array();

			/** @var $column SimpleXMLElement */
			foreach ($table->children() as $column) {
				$columnName = $column->getName();
				$columnValue = NULL;

				if (isset($column['ref'])) {
					list($tableName, $elementId) = explode('#', $column['ref']);
					$columnValue = $foreignKeys[$tableName][$elementId];
				} elseif (isset($column['is-NULL']) && ($column['is-NULL'] === 'yes')) {
					$columnValue = NULL;
				} else {
					$columnValue = $table->$columnName;
				}

				$insertArray[$columnName] = $columnValue;
			}

			$tableName = $table->getName();
			$db->exec_INSERTquery($tableName, $insertArray);

			if (isset($table['id'])) {
				$elementId = (string) $table['id'];
				$foreignKeys[$tableName][$elementId] = $db->sql_insert_id();
			}
		}
	}
}
?>