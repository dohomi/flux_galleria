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


use DMF\FluxGalleria\Controller\GalleriaController;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GalleriaPluginViewHelper
 *
 * This is a sample ViewHelper to run flux_galleria from any extension
 * In this example we use the picasa field for rendering one picasa useralbum
 * -> useralbum:galleriajs/Demo
 *
 * You can copy this and adjust it to your need. You need to modify the $settings which
 * overwrites the settings in the flux_galleria like as it would be used as flexform.
 *
 *
 * Call this ViewHelper like: <fg:galleriaFlux picasa="{media.caption}"/> and don't forget to
 * include the namespace for the ViewHelper
 *
 * @package DMF\ViewHelpers
 */
class GalleriaFluxViewHelper extends AbstractViewHelper {


	/**
	 * @var \DMF\FluxGalleria\Controller\GalleriaController
	 * @inject
	 */
	protected $galleriaController;

	/**
	 * Initialize
	 */
	public function initializeArguments() {
		$this->registerArgument('picasa', 'string', 'Plugin for picasa', FALSE, '');
		$this->registerArgument('galleriaId', 'string', 'Div element id of the galleria container', FALSE, '');
		$this->registerArgument('width', 'string', 'Width of galleria element', FALSE, 'auto');
		$this->registerArgument('height', 'string', 'Height of galleria element', FALSE, 252);
	}

	/**
	 * Render method
	 *
	 * @return string
	 */
	public function render() {

		// Important for picasa because of a Galleria bug - only one album can be selected currently
		$settings['one_item_only'] = 1;

		$settings['items']['1']['item'] = array(
			'type'          => 5,
			'picasa_method' => 'useralbum',
			'picasa'        => $this->arguments['picasa']
		);
		$settings['config']['width'] = $this->arguments['width'];
		$settings['config']['height'] = $this->arguments['height'];
		$galleriaId = ($this->arguments['galleriaId'] !== '') ? $this->arguments['galleriaId'] : uniqid('galleria');

		// overwrite the galleria ts settings and set the id of the content element
		$this->galleriaController->indexAction($settings, $galleriaId);

		return '<div id="' . $galleriaId . '"></div>';
	}


}

?>
