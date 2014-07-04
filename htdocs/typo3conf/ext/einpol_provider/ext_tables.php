<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Template Provider');
Tx_Flux_Core::registerProviderExtensionKey('einpol_provider', 'Page');
Tx_Flux_Core::registerProviderExtensionKey('einpol_provider', 'Content');

