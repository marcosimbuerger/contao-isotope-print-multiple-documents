<?php

namespace MarcoSimbuerger\IsotopePrintMultipleDocumentsBundle\Backend\ProductCollection;

use Contao\StringUtil;

/**
 * Class ButtonCallback.
 *
 * @package MarcoSimbuerger\IsotopePrintMultipleDocumentsBundle\Backend\ProductCollection
 */
class ButtonCallback {

    /**
     * Add print button.
     *
     * @param array $buttons
     *   The buttons.
     *
     * @return array
     *   The edited buttons array.
     */
    public function addPrintAllDocumentsButton(array $buttons): array {
        $buttons['print_all_documents'] = '<button type="submit" name="print_all_documents" id="print_all_documents" class="tl_submit">' . StringUtil::specialchars($GLOBALS['TL_LANG']['tl_iso_product_collection']['print']) . '</button> ';
        return $buttons;
    }

}
