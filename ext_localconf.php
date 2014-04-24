<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'DMF.' . $_EXTKEY,
	'Frontend',
	array(
		'Galleria' => 'index',
		
	),
	// non-cacheable actions
	array(
		
	)
);

## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder


?>