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
			} elseif ($key == 'galleriaHistoryPlugin' && (!$this->settings['enable']['history'] || !$this->settings['tsEnable']['history'])) {
				continue;
			} else {
				$this->getPageRendererAddFile($file);
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
	private function getPageRendererAddFile($fileObj) {
		if (is_array($fileObj)) {
			$file = $GLOBALS['TSFE']->tmpl->getFileName($fileObj['_typoScriptNodeValue']);
		} else {
			$file = $GLOBALS['TSFE']->tmpl->getFileName($fileObj);

		}
		$includeFunctionName = 'add';
		$funcArgumentList    = array();
		if ($file) {

			// JS
			if (strtolower(substr($file, -3)) === '.js') {
				$includeFunctionName .= 'Js';
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
				array_unshift($funcArgumentList, $file);

			}

			call_user_func_array(array($GLOBALS['TSFE']->getPageRenderer(), $includeFunctionName), $funcArgumentList);
		}
	}

	private function addJsInlineCode() {
		/*
		$block = '
		var GalleriaOptions = {
			thumbnails: {thumbnails},
			height: {height},
			width: {width},
			<f:if condition="{maxScaleRatio}">
				maxScaleRatio: {maxScaleRatio},
		</f:if>
		<f:if condition="{minScaleRatio}">
					minScaleRatio: {minScaleRatio},
		</f:if>
		imagePosition: "{imagePosition}",
		imageCrop: {imageCrop},
		show: {show},
		showCounter: {showCounter},
		showInfo: {showInfo},
		showImagenav: {showImagenav},
		lightbox: {lightbox},
		lightboxFadeSpeed: {lightboxFadeSpeed},
		lightboxTransitionSpeed: {lightboxTransitionSpeed},
		overlayBackground: "{overlayBackground}",
		overlayOpacity: {overlayOpacity},
		carousel: {carousel},
		carouselSpeed: {carouselSpeed},
		carouselSteps: {carouselSteps},
		transition: {transition},
		popupLinks: {popupLinks},
		responsive: {responsive},
		imagePan: {imagePan},
		{js-option}
		debug: {debug}
		}
		';
		*/
		$options = array();
		foreach ($this->settings['tsConfig'] as $key => $tsValue) {
			if ($tsValue && $tsValue != 'default') {
				$flexValue = $this->settings['config'][$key];
				// check the values from flexform and overwrite ts
				if ($flexValue && $flexValue != 'default') {
					$tsValue = $flexValue;
				}
				$options[] = $key . ':' . $this->escapeJsOption($tsValue);
			}
		}

		$block = '
			var GalleriaOptions = {
				' . implode(',', $options) . '
			};
		';
		// test

//		$block = '
//			var GalleriaOptions = {
//				width:611,height:250
//			};
//		';

		$block .= '
			Galleria.configure(GalleriaOptions);
			Galleria.run("#galleria_' . $this->record['uid'] . '");
		';

		$this->pageRenderer->addJsFooterInlineCode('galleria_' . $this->record['uid'], $block);

	}

	/**
	 * @param $option
	 *
	 * @return string
	 */
	private function escapeJsOption($option) {
		if (is_numeric($option) || $option == 'true' || $option == 'false') {
			return $option;
		} else {
			$option = str_replace(array("'", '"'), array('', ''), $option);

			return '"' . $option . '"';
		}
	}


	/**
	 * @param $item
	 * @param $iteration
	 *
	 * @return void
	 */
	private function addPicasaCode($item, $iteration) {

	}

	/**
	 * @param $item
	 * @param $iteration
	 *
	 * @return void
	 */
	private function addFlickrCode($item, $iteration) {

		$block = '
			var FlickrConfig_' . $iteration . ' = {
				max: ' . $item['max_flickr'] . ',
				imageSize: "' . $item['imageSize_flickr'] . '",
                thumbSize: "' . $item['thumbSize_flickr'] . '",
                sort: "' . $item['sort_flickr'] . '",
                description: ' . $item['description_flickr'] . '
			}

			var flickr = new Galleria.Flickr();
			flickr.setOptions(FlickrConfig_' . $iteration . ' );
            flickr.' . $item['flickr_method'] . '("' . $item['flickr'] . '", function (data) {
				Galleria.get(0).push(data);
			});
		';

		$this->pageRenderer->addJsFooterInlineCode('galleriaFlickr_' . $iteration, $block);
	}
}

?>