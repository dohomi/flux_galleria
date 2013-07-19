<?php
namespace DMF\FluxGalleria\ViewHelpers;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Dominic Garms <djgarms@gmail.com>
 *      DMFmedia GmbH <http://www.dmfmedia.de>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;

/**
 * Class FetchFilesViewHelper
 *
 * @package DMF\FluxGalleria\ViewHelpers
 */
class FetchFilesViewHelper extends AbstractTagBasedViewHelper {


	/**
	 * Initialize
	 */
	public function initializeArguments() {
		$this->registerArgument('folder', 'string', 'Directory from where to fetch files', TRUE, FALSE);
		$this->registerArgument('extension', 'mixed', 'Comma separated list of file extensions which are allowed', TRUE, FALSE);
		$this->registerArgument('recursive', 'int', 'Integer how many levels deep the files should get fetched.');
	}

	/**
	 * @return mixed
	 */
	public function render() {

		$files = $this->getFilenamesOfType();

		// add here the backup variables for the container
		$backupVars = array('files');
		$backups    = array();
		foreach ($backupVars as $var) {
			if ($this->templateVariableContainer->exists($var)) {
				$backups[$var] = $this->templateVariableContainer->get($var);
				$this->templateVariableContainer->remove($var);
			}
		}

		$this->templateVariableContainer->add('files', $files);
		$content = $this->renderChildren();
		$this->templateVariableContainer->remove('files');

		if (count($backups) > 0) {
			foreach ($backups as $var => $value) {
				$this->templateVariableContainer->add($var, $value);
			}
		}

		return $content;
	}

	/**
	 * Returns an array of files based on the extension argument
	 *
	 * @return array|bool
	 */
	protected function getFilenamesOfType() {
		$folder = $this->arguments['folder'];
		// check starting point for missing slash
		if (substr($folder, -1) != '/') {
			$folder = $folder . '/';
		} elseif (substr($folder, 0, 1) == '/') {
			$size   = strlen($folder);
			$folder = substr($folder, 1, $size - 1);
		}

		if (is_dir($folder)) {

			$files = GeneralUtility::getAllFilesAndFoldersInPath(
				$fileArr = array(),
				$folder,
				$extList = $this->arguments['extension'],
				$regDirs = 0,
				$recursiveLevel = $this->arguments['recursive'],
				$excludePattern = ''
			);

			if (is_array($files)) {
				return $files;
			}
		}

		return FALSE;

	}

}