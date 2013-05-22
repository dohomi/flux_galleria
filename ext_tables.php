<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

<<<<<<< HEAD
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Frontend',
	'Galleria Image & Video Gallery'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Galleria extension for TYPO3 CMS.');

## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder

# register plugin for flux configuration
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['fluxgalleria_frontend'] = 'pi_flexform';
\Tx_Flux_Core::registerConfigurationProvider('DMF\\FluxGalleria\\Provider\\PluginConfigurationProvider');
=======
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Galleria picture and video gallery');
>>>>>>> 1059c984c281503b25f29ae02aaea14612a74af1

?>