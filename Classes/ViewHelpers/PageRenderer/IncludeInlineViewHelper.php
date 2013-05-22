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

class Tx_FluxGalleria_ViewHelpers_PageRenderer_IncludeInlineViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractTagBasedViewHelper {


    /**
     * Initialize
     */
    public function initializeArguments() {
        $this->registerArgument('type', 'string', 'Type of inline block: css or js', TRUE, FALSE);
        $this->registerArgument('name', 'string', 'Name of inline block', TRUE, FALSE);
        $this->registerArgument('block', 'string', 'Inline code if not use of template children', FALSE, FALSE);
        $this->registerArgument('compress', 'boolean', 'Compress argument - see PageRenderer documentation', FALSE, TRUE);
        $this->registerArgument('forceOnTop', 'boolean', 'ForceOnTop argument - see PageRenderer documentation', FALSE, FALSE);
        $this->registerArgument('footer', 'boolean', 'Include file into Footer (js only)', FALSE, FALSE);


    }

    public function render() {
        $type = $this->arguments['type'];
        $block = $this->arguments['block'];
        if (!$block) {
            $block = $this->renderChildren();
        }
        $funcArgumentList = array(
            $this->arguments['name'],
            $block,
            $this->arguments['compress'],
            $this->arguments['forceOnTop']
        );
        $includeFunctionName = 'add';


        // js
        if ($block) {
            if ($type === 'js') {
                $includeFunctionName .= 'Js';
                if ($this->arguments['footer']) {
                    $includeFunctionName .= 'Footer';
                }
                $includeFunctionName .= 'InlineCode';

            } // css
            elseif ($type === 'css') {
                $includeFunctionName .= 'CssInlineBlock';
            }
            call_user_func_array(array($GLOBALS['TSFE']->getPageRenderer(), $includeFunctionName), $funcArgumentList);
        }


    }


}