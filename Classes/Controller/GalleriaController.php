<?php
namespace DMF\FluxGalleria\Controller;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012
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
class GalleriaController extends ActionController {

	/**
	 * @var array
	 */
	protected $record = array();

	/**
	 * @var array
	 */
	protected $options = array();

	/**
	 * @var bool
	 */
	protected $hasFlickrElement = FALSE;

	/**
	 * @var bool
	 */
	protected $hasPicasaElement = FALSE;

	/**
	 * @var \TYPO3\CMS\Core\Page\PageRenderer
	 * @inject
	 */
	protected $pageRenderer;

	/**
	 * @return void
	 */
	public function initializeAction() {

	}

	/**
	 * @return void
	 */
	public function indexAction() {
		$this->record = $this->configurationManager->getContentObject()->data;

		// add inline main js code
		$this->addJsInlineCode();

		// do additional actions depends on the type of each item
		$this->additionalTypeActions();

		// add galleria files
		$this->addFiles();

		// set scale for images
		$scaleArray = array('thumbWidth', 'thumbHeight', 'width', 'height');
		foreach ($scaleArray as $dim) {
			$this->settings['scale'][$dim] = ($this->settings['scale'][$dim]) ? $this->settings['scale'][$dim] : $this->settings['tsScale'][$dim];
		}

		$this->view->assign('record', $this->record);
		$this->view->assign('settings', $this->settings);
		$this->configurationManager->getContentObject()->data;


	}

	/**
	 * @return void
	 */
	private function additionalTypeActions() {
		$iteration = 1;
		foreach ($this->settings['items'] as $item) {
			$type = $item['item']['type'];
			if ($type == 4) {
				// flickr type
				$this->hasFlickrElement = TRUE;
				$this->addFlickrCode($item['item'], $iteration);
			} elseif ($type == 5) {
				//picasa type
				$this->hasPicasaElement = TRUE;
				$this->addPicasaCode($item['item'], $iteration);
			}
			$iteration++;
		}
	}

	/**
	 * @return void
	 */
	private function addFiles() {
		foreach ($this->settings['files'] as $key => $file) {
			if ($key == 'galleriaFlickrPlugin' && !$this->hasFlickrElement) {
				continue;
			} elseif ($key == 'galleriaPicasaPlugin' && !$this->hasPicasaElement) {
				continue;
			} elseif ($key == 'galleriaHistoryPlugin') {
				$tsHistory   = $this->settings['tsEnable']['history'];
				$flexHistory = $this->settings['enable']['history'];
				if (($tsHistory == 'true' && $flexHistory != 'false') || $flexHistory == 'true') {
					$this->addFilePageRenderer($file);
				}
			} else {
				$this->addFilePageRenderer($file);
			}
		}

	}

	/**
	 * Function adds files to the PageRenderer
	 *
	 * @param $file String
	 *
	 * @return void
	 */
	private function addFilePageRenderer($fileObj) {

		if (is_array($fileObj)) {
			$file = $GLOBALS['TSFE']->tmpl->getFileName($fileObj['_typoScriptNodeValue']);
		} else {
			$file = $GLOBALS['TSFE']->tmpl->getFileName($fileObj);
		}
		$includeFunctionName = 'add';

		$excludeFromConcatenation = ($fileObj['excludeFromConcatenation'] == 1) ? TRUE : FALSE;
		$compress                 = ($fileObj['compress'] == 1) ? TRUE : FALSE;
		$funcArgumentList         = array(
			$compress,
			$forceOnTop = FALSE,
			$allWrap = '',
			$excludeFromConcatenation
		);
		if ($file) {

			// JS
			if (strtolower(substr($file, -3)) === '.js') {
				$includeFunctionName .= 'Js';
				array_unshift($funcArgumentList, $type = 'text/javascript');
				array_unshift($funcArgumentList, $file);
				if ($fileObj['footer']) {
					$includeFunctionName .= 'Footer';
				}
				if ($fileObj['library']) {
					$includeFunctionName .= 'Library';
					array_unshift($funcArgumentList, $file);
				} else {
					$includeFunctionName .= 'File';
				}
			} // CSS
			elseif (strtolower(substr($file, -4)) === '.css') {
				$includeFunctionName .= 'CssFile';
				array_unshift($funcArgumentList, $title = '');
				array_unshift($funcArgumentList, $media = 'all');
				array_unshift($funcArgumentList, $rel = 'stylesheet');
				array_unshift($funcArgumentList, $file);

			}
			call_user_func_array(array($GLOBALS['TSFE']->getPageRenderer(), $includeFunctionName), $funcArgumentList);
		}
	}

	/**
	 * @return void
	 */
	private function addJsInlineCode() {

		foreach ($this->settings['tsConfig'] as $key => $tsValue) {
			if ($tsValue) {
				$flexValue = $this->settings['config'][$key];
				// check the values from flexform and overwrite ts
				if ($flexValue && $flexValue != 'default') {
					$tsValue = $flexValue;
				}
				// set $this->options array
				if ($tsValue != 'default') {
					$this->escapeJsOption($key, $tsValue);
				}
			}
		}
		// add additionalconfig from flexform
		if ($this->settings['additionalconfig']['js']) {
			$this->options[] = rtrim(preg_replace('/\s+/', ' ', $this->settings['additionalconfig']['js']), ',');
		}

		$block = '
			var galleria = Galleria;
			galleria.configure({
				' . implode(',', $this->options) . '
			});
			galleria.run("#galleria_' . $this->record['uid'] . '");
		';

		$this->pageRenderer->addJsFooterInlineCode('galleria_' . $this->record['uid'], $block);

	}

	/**
	 * This function sets the JS option array according to its type
	 *
	 * @param $key
	 * @param $option
	 *
	 * @return void
	 */
	private function escapeJsOption($key, $option) {
		if (is_numeric($option) || $option == 'true' || $option == 'false') {
			$this->options[] = $key . ':' . $option;
		} elseif ($key == 'extend') {
			$this->options[] = $key . ':' . trim(preg_replace('/\s+/', ' ', $option));
		} else {
			$option = str_replace(array("'", '"'), array('', ''), $option);

			$this->options[] = $key . ':' . '"' . $option . '"';
		}
	}


	/**
	 * @param $item
	 * @param $iteration
	 *
	 * @return void
	 */
	private function addPicasaCode($item, $iteration) {

		$block = '
			var picasa = new Galleria.Picasa();
			picasa.setOptions({
				max: ' . $item['max_picasa'] . ',
				imageSize: "' . $item['imageSize_picasa'] . '",
                thumbSize: "' . $item['thumbSize_picasa'] . '"
			}).' . $item['picasa_method'] . '("' . $item['picasa'] . '", function (data) {
				galleria.get(0).push(data);
			});
		';

		$this->pageRenderer->addJsFooterInlineCode('galleriaPicasa_' . $this->record['uid'] . '_' . $iteration, $block);
	}

	/**
	 * @param $item
	 * @param $iteration
	 *
	 * @return void
	 */
	private function addFlickrCode($item, $iteration) {

		$block = '
			var flickr = new Galleria.Flickr();
			flickr.setOptions({
				max: ' . $item['max_flickr'] . ',
				imageSize: "' . $item['imageSize_flickr'] . '",
                thumbSize: "' . $item['thumbSize_flickr'] . '",
                sort: "' . $item['sort_flickr'] . '",
                description: ' . $item['description_flickr'] . '
			}).' . $item['flickr_method'] . '("' . $item['flickr'] . '", function (data) {
				galleria.get(0).push(data);
			});
		';

		$this->pageRenderer->addJsFooterInlineCode('galleriaFlickr_' . $this->record['uid'] . '_' . $iteration, $block);
	}
}

?>