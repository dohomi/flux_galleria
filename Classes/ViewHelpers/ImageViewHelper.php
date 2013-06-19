<?php
namespace DMF\FluxGalleria\ViewHelpers;

	/*                                                                        *
	 * This script is part of the TYPO3 project - inspiring people to share!  *
	 *                                                                        *
	 * TYPO3 is free software; you can redistribute it and/or modify it under *
	 * the terms of the GNU General Public License version 2 as published by  *
	 * the Free Software Foundation.                                          *
	 *                                                                        *
	 * This script is distributed in the hope that it will be useful, but     *
	 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
	 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
	 * Public License for more details.                                       *
	 *                                                                        */
/**
 * Resizes a given image (if required) and renders the respective img tag
 *
 * = Examples =
 *
 * <code title="Default">
 * <f:image src="EXT:myext/Resources/Public/typo3_logo.png" alt="alt text" />
 * </code>
 * <output>
 * <img alt="alt text" src="typo3conf/ext/myext/Resources/Public/typo3_logo.png" width="396" height="375" />
 * or (in BE mode):
 * <img alt="alt text" src="../typo3conf/ext/viewhelpertest/Resources/Public/typo3_logo.png" width="396" height="375" />
 * </output>
 *
 * <code title="Inline notation">
 * {f:image(src: 'EXT:viewhelpertest/Resources/Public/typo3_logo.png', alt: 'alt text', minWidth: 30, maxWidth: 40)}
 * </code>
 * <output>
 * <img alt="alt text" src="../typo3temp/pics/f13d79a526.png" width="40" height="38" />
 * (depending on your TYPO3s encryption key)
 * </output>
 *
 * <code title="non existing image">
 * <f:image src="NonExistingImage.png" alt="foo" />
 * </code>
 * <output>
 * Could not get image resource for "NonExistingImage.png".
 * </output>
 */
class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper {

	/**
	 * Initialize arguments.
	 *
	 * @return void
	 */
	public function initializeArguments() {
		parent::initializeArguments();
	}

	/**
	 * Resizes a given image (if required) and renders the respective img tag
	 *
	 * @see http://typo3.org/documentation/document-library/references/doc_core_tsref/4.2.0/view/1/5/#id4164427
	 *
	 * @param string  $src
	 * @param string  $width              width of the image. This can be a numeric value representing the fixed width of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param string  $height             height of the image. This can be a numeric value representing the fixed height of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width for possible options.
	 * @param integer $minWidth           minimum width of the image
	 * @param integer $minHeight          minimum height of the image
	 * @param integer $maxWidth           maximum width of the image
	 * @param integer $maxHeight          maximum height of the image
	 * @param boolean $treatIdAsReference given src argument is a sys_file_reference record
	 *
	 * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception
	 * @return string rendered tag.
	 */
	public function render($src, $width = NULL, $height = NULL, $minWidth = NULL, $minHeight = NULL, $maxWidth = NULL, $maxHeight = NULL, $treatIdAsReference = FALSE) {
		if (TYPO3_MODE === 'BE') {
			$this->simulateFrontendEnvironment();
		}
		$setup = array(
			'width'              => $width,
			'height'             => $height,
			'minW'               => $minWidth,
			'minH'               => $minHeight,
			'maxW'               => $maxWidth,
			'maxH'               => $maxHeight,
			'treatIdAsReference' => $treatIdAsReference
		);
		if (TYPO3_MODE === 'BE' && substr($src, 0, 3) === '../') {
			$src = substr($src, 3);
		}
		$imageInfo                      = $this->contentObject->getImgResource($src, $setup);
		$GLOBALS['TSFE']->lastImageInfo = $imageInfo;
		if (!is_array($imageInfo)) {
			if (TYPO3_MODE === 'BE') {
				$this->resetFrontendEnvironment();
			}
			throw new \TYPO3\CMS\Fluid\Core\ViewHelper\Exception('Could not get image resource for "' . htmlspecialchars($src) . '".', 1253191060);
		}
		$imageInfo[3]                    = \TYPO3\CMS\Core\Utility\GeneralUtility::png_to_gif_by_imagemagick($imageInfo[3]);
		$GLOBALS['TSFE']->imagesOnPage[] = $imageInfo[3];
		$imageSource                     = $GLOBALS['TSFE']->absRefPrefix . \TYPO3\CMS\Core\Utility\GeneralUtility::rawUrlEncodeFP($imageInfo[3]);
		if (TYPO3_MODE === 'BE') {
			$imageSource = '../' . $imageSource;
			$this->resetFrontendEnvironment();
		}
		$this->tag->addAttribute('src', $imageSource);
		$this->tag->addAttribute('width', $imageInfo[0]);
		$this->tag->addAttribute('height', $imageInfo[1]);
		//the alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
		if (empty($this->arguments['alt'])) {
			$this->tag->addAttribute('alt', '');
		}
		if (empty($this->arguments['title']) && !empty($this->arguments['alt'])) {
			$this->tag->addAttribute('title', $this->arguments['alt']);
		}

		// just modified for flux_galleria modified
		if (!$this->tag->getAttribute('data-linkuid')) {
			$this->tag->removeAttribute('data-link');
		}

		$this->tag->removeAttribute('data-linkuid');

		return $this->tag->render();
	}


}

?>