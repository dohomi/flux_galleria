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

class Tx_FluxGalleria_ViewHelpers_PageRenderer_IncludeFileViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractTagBasedViewHelper {

    /**
     * Initialize
     */
    public function initializeArguments() {
        $this->registerArgument('type', 'string', 'Type argument - see PageRenderer documentation', FALSE, 'text/javascript');
        $this->registerArgument('compress', 'boolean', 'Compress argument - see PageRenderer documentation', FALSE, TRUE);
        $this->registerArgument('forceOnTop', 'boolean', 'ForceOnTop argument - see PageRenderer documentation', FALSE, FALSE);
        $this->registerArgument('allWrap', 'string', 'AllWrap argument - see PageRenderer documentation', FALSE, '');
        $this->registerArgument('excludeFromConcatenation', 'string', 'ExcludeFromConcatenation argument - see PageRenderer documentation', FALSE, FALSE);
        $this->registerArgument('rel', 'string', 'Rel tag (css only)', FALSE, 'stylesheet');
        $this->registerArgument('media', 'string', 'Media tag (css only)', FALSE, 'all');
        $this->registerArgument('title', 'string', 'Title for css (css only)', FALSE, '');
        $this->registerArgument('footer', 'boolean', 'Include file into Footer (js only)', FALSE, FALSE);
        $this->registerArgument('library', 'boolean', 'Include file as JS library (js only', FALSE, FALSE);
        $this->registerArgument('file', 'string', 'css or js file', TRUE, NULL);


    }

    public function render() {
        $file = $GLOBALS['TSFE']->tmpl->getFileName($this->arguments['file']);
        $includeFunctionName = 'add';

        if ($file) {
            $funcArgumentList = array(
                $this->arguments['compress'],
                $this->arguments['forceOnTop'],
                $this->arguments['allWrap'],
                $this->arguments['excludeFromConcatenation']
            );

            // JS
            if (strtolower(substr($file, -3)) === '.js') {
                $includeFunctionName .= 'Js';
                array_unshift($funcArgumentList, $this->arguments['type']);
                array_unshift($funcArgumentList, $file);
                if ($this->arguments['footer']) {
                    $includeFunctionName .= 'Footer';
                }
                if ($this->arguments['library']) {
                    $includeFunctionName .= 'Library';
                    array_unshift($funcArgumentList, $file);
                } else {
                    $includeFunctionName .= 'File';
                }
            }
            // CSS
            elseif (strtolower(substr($file, -4)) === '.css') {
                $includeFunctionName .= 'CssFile';
                array_unshift($funcArgumentList, $this->arguments['title']);
                array_unshift($funcArgumentList, $this->arguments['media']);
                array_unshift($funcArgumentList, $this->arguments['rel']);
                array_unshift($funcArgumentList, $file);

            }

            call_user_func_array(array($GLOBALS['TSFE']->getPageRenderer(), $includeFunctionName), $funcArgumentList);
        }

    }

}