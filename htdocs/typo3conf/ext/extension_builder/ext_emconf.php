<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "extension_builder".
 *
 * Auto generated 19-05-2014 20:51
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Extension Builder',
	'description' => 'The Extension Builder helps you build and manage your Extbase based TYPO3 extensions.',
	'category' => 'module',
	'shy' => false,
	'version' => '6.2.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => NULL,
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => true,
	'createDirs' => 'uploads/tx_extensionbuilder/backups',
	'modify_tables' => '',
	'clearcacheonload' => false,
	'lockType' => '',
	'author' => 'Nico de Haen',
	'author_email' => 'mail@ndh-websolutions.de',
	'author_company' => '',
	'CGLcompliance' => NULL,
	'CGLcompliance_note' => NULL,
	'constraints' => 
	array (
		'depends' => 
		array (
			'typo3' => '6.1.0-6.2.99',
		),
		'suggests' => 
		array (
			'phpunit' => '',
		),
		'conflicts' => 
		array (
		),
	),
);

?>