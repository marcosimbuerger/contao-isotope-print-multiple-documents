<?php

use MarcoSimbuerger\IsotopePrintMultipleDocuments\Backend\ProductCollection\ButtonCallback;
use MarcoSimbuerger\IsotopePrintMultipleDocuments\Backend\ProductCollection\DcaCallback;

// Calls the 'DC_TableExtension' of the 'contao-data-container-extension' module.
$GLOBALS['TL_DCA']['tl_iso_product_collection']['config']['dataContainer'] = 'TableExtension';

// Callbacks.
$GLOBALS['TL_DCA']['tl_iso_product_collection']['config']['onload_callback'][] = [DcaCallback::class, 'onLoad'];
$GLOBALS['TL_DCA']['tl_iso_product_collection']['select']['buttons_callback'][] = [ButtonCallback::class, 'addPrintAllDocumentsButton'];
