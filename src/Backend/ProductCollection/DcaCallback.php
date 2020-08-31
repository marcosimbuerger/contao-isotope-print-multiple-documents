<?php

namespace MarcoSimbuerger\IsotopePrintMultipleDocuments\Backend\ProductCollection;

use Contao\Backend;
use Contao\DC_Table;
use Contao\Environment;
use Contao\Input;

/**
 * Class DcaCallback.
 *
 * @package MarcoSimbuerger\IsotopePrintMultipleDocuments\Backend\ProductCollection
 */
class DcaCallback extends Backend {

    /**
     * Called by onload_callback.
     *
     * @param \Contao\DC_Table $dataContainer
     *   The Contao data container (DC).
     */
    public function onLoad(DC_Table $dataContainer): void {
        // Is available after button submission.
        if (Input::post('FORM_SUBMIT') === 'tl_select') {
            if (isset($_POST[DocumentPrinter::PRINT_ALL_DOCUMENTS_BUTTON_ID])) {
                // Replace default 'select' action with 'print' action.
                $this->redirect(str_replace('act=select', 'act=' . DocumentPrinter::PRINT_ALL_DOCUMENTS_ACTION_NAME, Environment::get('request')));
            }
        }

        // Is available on the 'print all documents' page.
        if (Input::get('act') === DocumentPrinter::PRINT_ALL_DOCUMENTS_ACTION_NAME) {
            $dataContainer->{DocumentPrinter::PRINT_ALL_DOCUMENTS_ACTION_NAME} = function() {
                /** @var \MarcoSimbuerger\IsotopePrintMultipleDocuments\Backend\ProductCollection\DocumentPrinter $documentPrinter */
                $documentPrinter = new DocumentPrinter();
                return $documentPrinter->printAllDocuments();
            };
        }
    }

}
