<?php
namespace DMF\FluxGalleria\ViewHelpers;

/**
 * @author     Dominic Garms, DMFmedia GmbH
 * @package    dmf_template
 * @subpackage ViewHelpers/PageRenderer
 */

use TYPO3\CMS\Core\Resource\FileCollectionRepository;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Dominic Garms <djgarms@gmail.com>, DMFmedia GmbH
 *
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
class FileCollectionViewHelper extends AbstractTagBasedViewHelper {

	/**
	 * Initialize
	 */
	public function initializeArguments() {
		$this->registerArgument('uid', 'string', 'Uid of file collection', TRUE, FALSE);
	}

	/**
	 * @return mixed
	 */
	public function render() {

		$fileCollection = $this->getFilesOfCollection();

		// add here the backup variables for the container
		$backupVars = array('fileCollection');
		$backups    = array();
		foreach ($backupVars as $var) {
			if ($this->templateVariableContainer->exists($var)) {
				$backups[$var] = $this->templateVariableContainer->get($var);
				$this->templateVariableContainer->remove($var);
			}
		}

		$this->templateVariableContainer->add('fileCollection', $fileCollection);
		$content = $this->renderChildren();
		$this->templateVariableContainer->remove('fileCollection');

		if (count($backups) > 0) {
			foreach ($backups as $var => $value) {
				$this->templateVariableContainer->add($var, $value);
			}
		}

		return $content;
	}

	/**
	 * Returns all file objects as array
	 *
	 * @return array
	 */
	protected function getFilesOfCollection() {
		$uid = $this->arguments['uid'];

		/** @var FileRepository $fileRepository */
		$fileRepository = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Resource\\FileRepository');
		$fileCollection = $fileRepository->findByRelation('sys_file_collection', 'files', $uid);

		foreach ($fileCollection as $item) {
			/** @var FileReference $item */
			$files[] = $item->toArray();
		}

		return $files;
	}
}