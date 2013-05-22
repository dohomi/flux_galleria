<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
<<<<<<< HEAD
	'DMF.' . $_EXTKEY,
	'Frontend',
	array(
		'Galleria' => 'index',
=======
	'TYPO3.' . $_EXTKEY,
	'Galleria',
	array(
>>>>>>> 1059c984c281503b25f29ae02aaea14612a74af1
		
	),
	// non-cacheable actions
	array(
		
	)
);

<<<<<<< HEAD
## EXTENSION BUILDER DEFAULTS END TOKEN - Everything BEFORE this line is overwritten with the defaults of the extension builder
=======
>>>>>>> 1059c984c281503b25f29ae02aaea14612a74af1
?>