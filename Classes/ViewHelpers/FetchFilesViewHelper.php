<?php
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

/**
 * @author Dominic Garms, DMFmedia GmbH
 * @package dmf_template
 * @subpackage ViewHelpers/PageRenderer
 */

class Tx_FluxGalleria_ViewHelpers_FetchFilesViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractTagBasedViewHelper {


	/**
	 * Initialize
	 */
	public function initializeArguments() {
		$this->registerArgument('dir', 'string', 'Directory from where to fetch files', TRUE, FALSE);
		$this->registerArgument('extension', 'mixed', 'Comma separated list of file extensions which are allowed', TRUE, FALSE);

	}

	/**
	 *
	 */
	public function render() {

		$files = $this->getFilenamesOfType($this->arguments['dir']);

		// add here the backup variables for the container
		$backupVars = array('files');
		$backups = array();
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
	 * Get an array of all with extension $extension in $dir
	 *
	 *
	 * @param string $dir
	 * @return array
	 */
	protected function getFilenamesOfType($dir) {
		$allowedExtension = t3lib_div::trimExplode(',', $this->arguments['extension']);
		$relative = $dir;
		if (substr($dir, 0, 1) != '/') {
			$dir = PATH_site . $dir;
		}
		$files = scandir($dir);
		if (is_array($files)) {


			foreach ($files as $k => $file) {
				$pathinfo = pathinfo($dir . $file);
				if ($pathinfo['extension'] == '') {
					unset($files[$k]);
				} elseif (is_dir($dir . $file)) {
					unset($files[$k]);
				} elseif ($allowedExtension && !in_array($pathinfo['extension'], $allowedExtension)) {
					unset($files[$k]);
				} else {
					$files[$k] = $relative . '/' . $file;
				}
			}
			sort($files);

			return $files;
		}

		return false;

	}

}