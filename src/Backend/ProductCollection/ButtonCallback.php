<?php

namespace MarcoSimbuerger\IsotopePrintMultipleDocuments\Backend\ProductCollection;

use Contao\StringUtil;

/**
 * Class ButtonCallback.
 *
 * @package MarcoSimbuerger\IsotopePrintMultipleDocuments\Backend\ProductCollection
 */
class ButtonCallback {

    /**
     * Add print button.
     *
     * @param array $butttons
     *   The buttons.
     *
     * @return array
     *   The edited buttons array.
     */
    public function addPrintAllDocumentsButton(array $butttons) {
        $butttons['print_all_documents'] = '<button type="submit" name="print_all_documents" id="print_all_documents" class="tl_submit">' . StringUtil::specialchars($GLOBALS['TL_LANG']['tl_iso_product_collection']['print']) . '</button> ';
        return $butttons;
    }

}
