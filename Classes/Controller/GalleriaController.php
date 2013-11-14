<?php
namespace DMF\FluxGalleria\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Dominic Garms <djgarms@gmail.com>, DMFmedia GmbH - http://www.dmfmedia.de
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


use TYPO3\CMS\Core\Resource\Collection\AbstractFileCollection;
use TYPO3\CMS\Core\Resource\Collection\FolderBasedFileCollection;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class GalleriaController
 *
 * @package DMF\FluxGalleria\Controller
 */
class GalleriaController extends ActionController {

	/**
	 * @var string
	 */
	protected $recordUid;

	/**
	 * @var array
	 */
	protected $options = array();

	/**
	 * @var array
	 */
	protected $jsBlock = array();

	/**
	 * @var bool
	 */
	protected $hasFlickrElement = FALSE;

	/**
	 * @var bool
	 */
	protected $hasPicasaElement = FALSE;

	/**
	 * @var array
	 */
	protected $dataJson = array();

	/**
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 * @inject
	 */
	protected $cObj;

	/**
	 * @var \TYPO3\CMS\Core\Page\PageRenderer
	 * @inject
	 */
	protected $pageRenderer;

	/**
	 * @var \TYPO3\CMS\Core\Resource\FileCollectionRepository
	 * @inject
	 */
	protected $fileCollectionRepository;

	/**
	 * Main function of GalleriaController.
	 * With $settings and $recordUid the whole configuration can get overwritten,
	 * a sample how this works is the GalleriaFluxViewHelper. More options can get
	 * included if necessary.
	 *
	 * @param array $settings
	 * @param string $recordUid
	 */
	public function indexAction($settings = array(), $recordUid = '') {

		if (!empty($settings)) {
			$this->settings = $this->configurationManager->getConfiguration(
				ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'fluxgalleria', 'frontend'
			);

			$this->configurationManager->setConfiguration(
				$this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK, 'fluxgalleria', 'frontend')
			);

			$this->settings = array_merge($this->settings, $settings);
		}

		$this->recordUid = ($recordUid !== '') ? $recordUid : 'g_' . $this->configurationManager->getContentObject()->data['uid'];

		// set scale for images
		$scaleArray = array('thumbWidth', 'thumbHeight', 'width', 'height');
		foreach ($scaleArray as $dim) {
			$this->settings['scale'][$dim] = ($this->settings['scale'][$dim]) ? $this->settings['scale'][$dim] : $this->settings['tsScale'][$dim];
		}

		// do additional actions depends on the type of each item
		$this->additionalTypeActions();

		// build json data if enabled
		if ($this->settings['useJson'] != 0) {
			$this->buildDataJson();
		}

		// add inline main js code
		$this->addJsInlineCode();

		// add galleria files
		$this->addFiles();

		// don't render the view if $settings is set-> call from ViewHelper
		if (empty($settings)) {
			// set boolean for debugFluid
			$this->settings['debugFluid'] = ($this->settings['debugFluid'] == 'true') ? TRUE : FALSE;
			$this->view->assign('recordUid', $this->recordUid);
			$this->view->assign('settings', $this->settings);
			$noJson = ($this->settings['useJson'] == 0) ? TRUE : FALSE;
			$this->view->assign('noJson', $noJson);
		}
	}

	/**
	 * Builds the data array if JSON output enabled
	 */
	protected function buildDataJson() {

		$iteration = 0;

		foreach ($this->settings['items'] as $item) {
			$type = $item['item']['type'];
			//$keyDeclaration = array('image', 'thumb', 'title', 'link', 'description', 'layer', 'video', 'iframe');
			$fieldType = array();
			switch ($type) {
				case 1:
					// image type
					$fieldType[$iteration]['image'] = $this->getScaledImage($item['item']['original']);
					$fieldType[$iteration]['thumb'] = ($item['item']['thumb']) ? $this->getScaledImage($item['item']['thumb'], TRUE) : $this->getScaledImage($item['item']['original'], TRUE);
					$fieldType[$iteration]['title'] = $item['item']['title'];
					$fieldType[$iteration]['link'] = $this->cObj->getTypoLink_URL($item['item']['link']);
					$fieldType[$iteration]['description'] = $item['item']['description'];
					$fieldType[$iteration]['layer'] = $item['item']['layer'];
					$iteration++;
					break;
				case 2:
					// video type
					$fieldType[$iteration]['video'] = $item['item']['video'];
					$fieldType[$iteration]['thumb'] = $this->getScaledImage($item['item']['thumb_video'], TRUE);
					$fieldType[$iteration]['title'] = $item['item']['title_video'];
					$fieldType[$iteration]['description'] = $item['item']['description_video'];
					$fieldType[$iteration]['layer'] = $item['item']['layer_video'];
					$iteration++;
					break;

				case 3:
					// iframe type
					$fieldType[$iteration]['iframe'] = $item['item']['iframe'];
					$fieldType[$iteration]['thumb'] = $this->getScaledImage($item['item']['thumb_iframe'], TRUE);
					$fieldType[$iteration]['title'] = $item['item']['title_iframe'];
					$fieldType[$iteration]['description'] = $item['item']['description_iframe'];
					$fieldType[$iteration]['layer'] = $item['item']['layer_iframe'];
					$iteration++;
					break;

				case 6:
					// folder type
					if (is_array($item['item']['folder_files'])) {
						foreach ($item['item']['folder_files'] as $file) {
							$fieldType[$iteration]['image'] = $this->getScaledImage($file);
							$fieldType[$iteration]['thumb'] = $this->getScaledImage($file, TRUE);
							$iteration++;
						}
					}
					break;

				case 7:
					// file collection
					if (is_array($item['item']['collection_files'])) {
						foreach ($item['item']['collection_files'] as $file) {
							$fieldType[$iteration]['image'] = $this->getScaledImage($file['url']);
							$fieldType[$iteration]['thumb'] = $this->getScaledImage($file['url'], TRUE);
							$fieldType[$iteration]['title'] = $file['title'];
							$fieldType[$iteration]['description'] = $file['description'];
							$iteration++;
						}
					}
					break;
				default:
					break;
			}

			foreach ($fieldType as $i => $data) {
				foreach ($data as $dataKey => $value) {
					if ($value) {
						$this->dataJson[$i][$dataKey] = $value;
					}
				}
			}
		}

	}

	/**
	 * Returns an array of files based on the folder name
	 *
	 * @param $folder
	 * @param mixed $extList CSV list of allowed file extensions
	 * @param int $recursiveLevel
	 *
	 * @return array|bool
	 */
	protected function fetchImagesFromFolder($folder, $extList = '', $recursiveLevel = 0) {
		// check starting point for missing slash
		if (substr($folder, -1) != '/') {
			$folder = $folder . '/';
		} elseif (substr($folder, 0, 1) == '/') {
			$size = strlen($folder);
			$folder = substr($folder, 1, $size - 1);
		}

		if (is_dir($folder)) {

			$files = GeneralUtility::getAllFilesAndFoldersInPath(
				$fileArr = array(),
				$folder,
				$extList,
				$regDirs = 0,
				$recursiveLevel,
				$excludePattern = ''
			);

			if (is_array($files)) {
				return $files;
			}
		}

		return FALSE;

	}


	/**
	 * @param $path
	 * @param bool $thumb
	 *
	 * @return string
	 */
	protected function getScaledImage($path, $thumb = FALSE) {
		if ($path) {
			if ($thumb !== FALSE) {
				$conf['width'] = $this->settings['scale']['thumbWidth'];
				$conf['height'] = $this->settings['scale']['thumbHeight'];
			} else {
				$conf['width'] = $this->settings['scale']['width'];
				$conf['height'] = $this->settings['scale']['height'];
			}

			$imgResource = $this->cObj->getImgResource($path, $conf);

			return $imgResource[3];
		} else {
			return '';
		}

	}

	/**
	 * @return void
	 */
	protected function additionalTypeActions() {
		foreach ($this->settings['items'] as $key => $item) {
			$type = $item['item']['type'];

			switch ($type) {
				case 4:
					// flickr type
					$this->hasFlickrElement = TRUE;
					$this->addFlickrCode($item['item'], $key);
					break;
				case 5:
					// picasa type
					$this->hasPicasaElement = TRUE;
					$this->addPicasaCode($item['item'], $key);
					break;
				case 6:
					// folder type
					if ($folderFiles = $this->fetchImagesFromFolder($item['item']['folder'], $item['item']['extension'], $item['item']['folder_recursive'])) {
						$this->settings['items'][$key]['item']['folder_files'] = $folderFiles;
					}
					break;

				case 7:
					// file collection
					if ($collection = $this->getFileCollectionItems($item['item']['collection'])) {
						$this->settings['items'][$key]['item']['collection_files'] = $collection;
					}
					break;
				default:
					break;
			}
		}
	}

	/**
	 * @param $uid collection uid from flexform
	 *
	 * @return array|void
	 */
	protected function getFileCollectionItems($uid) {
		$fileCollection = $this->fileCollectionRepository->findByUid($uid);

		/** @var AbstractFileCollection $fileCollection */
		$fileCollection->loadContents();
		$items = $fileCollection->getItems();

		foreach ($items as $item) {
			/** @var FileReference $item */
			$files[] = $item->toArray();
		}

		return $files;

	}

	/**
	 * @return void
	 */
	protected function addFiles() {
		foreach ($this->settings['files'] as $key => $file) {
			if ($key == 'galleriaFlickrPlugin' && !$this->hasFlickrElement) {
				continue;
			} elseif ($key == 'galleriaPicasaPlugin' && !$this->hasPicasaElement) {
				continue;
			} elseif ($key == 'galleriaHistoryPlugin') {
				$tsHistory = $this->settings['tsEnable']['history'];
				$flexHistory = $this->settings['enable']['history'];
				if (($tsHistory == 'true' && $flexHistory != 'false') || $flexHistory == 'true') {
					$this->addFilePageRenderer($file);
				}
			} else {
				$this->addFilePageRenderer($file);
			}
		}


		ksort($this->jsBlock);
		$inlineCode = implode("\n", $this->jsBlock);

		$this->pageRenderer->addJsFooterInlineCode('galleriaMain_' . $this->recordUid, $inlineCode);
	}

	/**
	 * Function adds files to the PageRenderer
	 *
	 * @param $file String
	 *
	 * @return void
	 */
	protected function addFilePageRenderer($fileObj) {

		if (is_array($fileObj)) {
			$file = $GLOBALS['TSFE']->tmpl->getFileName($fileObj['_typoScriptNodeValue']);
		} else {
			$file = $GLOBALS['TSFE']->tmpl->getFileName($fileObj);
		}
		$includeFunctionName = 'add';

		$excludeFromConcatenation = ($fileObj['excludeFromConcatenation'] == 1) ? TRUE : FALSE;
		$compress = ($fileObj['compress'] == 1) ? TRUE : FALSE;
		$funcArgumentList = array(
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
	protected function addJsInlineCode() {

		foreach ($this->settings['tsConfig'] as $key => $tsValue) {

			$flexValue = $this->settings['config'][$key];
			// check the values from flexform and overwrite ts
			if ($flexValue && $flexValue != 'default') {
				$tsValue = $flexValue;
			}
			// set $this->options array
			if ($tsValue != 'default' && $tsValue) {
				$this->escapeJsOption($key, $tsValue);
			}

		}
		// add additionalconfig from flexform
		if ($this->settings['additionalconfig']['js']) {
			$this->options[] = rtrim(preg_replace(' / \s +/', ' ', $this->settings['additionalconfig']['js']), ',');
		}
		$block = '';

		if ($this->settings['useJson'] != 0 && !empty($this->dataJson)) {
			$block .= 'var galleriaData = ' . json_encode($this->dataJson) . ';';
			$this->options[] = 'dataSource: galleriaData';
		}
		$block .= '
			var galleria = Galleria;
			galleria.configure({
				' . implode(',', $this->options) . '
			});
			galleria.run("#' . $this->recordUid . '");
		';

		$this->jsBlock[0] = $block;

	}

	/**
	 * This function sets the JS option array according to its type
	 *
	 * @param $key
	 * @param $option
	 *
	 * @return void
	 */
	protected function escapeJsOption($key, $option) {
		if (is_numeric($option) || $option == 'true' || $option == 'false') {
			$this->options[] = $key . ':' . $option;
		} elseif ($key == 'extend') {
			$this->options[] = $key . ':' . trim(preg_replace('/\s +/', ' ', $option));
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
	protected function addPicasaCode($item, $iteration) {

		$options = array();
		if ($item['max']) {
			$options[] = 'max: ' . $item['max_picasa'];
		}
		if ($item['imageSize']) {
			$options[] = 'max: "' . $item['imageSize_picasa'] . '"';
		}
		if ($item['thumbSize']) {
			$options[] = 'max: "' . $item['thumbSize_picasa'] . '"';
		}

		if ($this->settings['one_item_only'] == 1 || count($this->settings['items']) === 1) {
			// if picasa_only or only one item as picasa image gallery is set
			if ($item['picasa_method'] === trim('useralbum')) {
				list($user, $album) = GeneralUtility::trimExplode(',', $item['picasa']);
				$item['picasa'] = ($album === NULL) ? $user : $user . '/' . $album;
			}
			// if picasa string already sets the method exclude it
			$this->options[] = (strpos($item['picasa'], $item['picasa_method']) === FALSE)
				? 'picasa: "' . $item['picasa_method'] . ':' . $item['picasa'] . '"'
				: 'picasa: "' . $item['picasa'] . '"';
			if (!empty($options)) {
				$this->options[] = 'picasaOptions: {
					' . implode(',', $options) . '
				}';
			}
		} else {
			// add picasa code to the already instantiated galleria
			if ($item['picasa_method'] === trim('useralbum')) {
				list($user, $album) = GeneralUtility::trimExplode(',', $item['picasa']);
				$item['picasa'] = '"' . $user . '","' . $album . '"';
			}
			$block = '
				var picasa = new Galleria.Picasa();
				picasa.setOptions({
					' . implode(',', $options) . '
				}).' . $item['picasa_method'] . '(' . $item['picasa'] . ', function (data) {
				galleria.get(0).push(data);
			});
			';

			$this->jsBlock[$iteration] = $block;

		}


	}

	/**
	 * @param $item
	 * @param $iteration
	 *
	 * @return void
	 */
	protected function addFlickrCode($item, $iteration) {
		$options = array();
		if ($item['max']) {
			$options[] = 'max: ' . $item['max_flickr'];
		}
		if ($item['imageSize_flickr']) {
			$options[] = 'imageSize: "' . $item['imageSize_flickr'] . '"';
		}
		if ($item['thumbSize_flickr']) {
			$options[] = 'thumbSize: "' . $item['thumbSize_flickr'] . '"';
		}
		if ($item['sort_flickr']) {
			$options[] = 'sort: "' . $item['sort_flickr'] . '"';
		}
		if ($item['description_flickr']) {
			$options[] = 'description: ' . $item['description_flickr'];
		}

		$block = '
			var flickr = new Galleria.Flickr();
			flickr.setOptions({
				' . implode(',', $options) . '
			}).' . $item['flickr_method'] . '("' . $item['flickr'] . '", function (data) {
				galleria.get(0).push(data);
			});
		';

		$this->jsBlock[$iteration] = $block;

	}


}

?>