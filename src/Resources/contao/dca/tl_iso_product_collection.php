<?php

use MarcoSimbuerger\IsotopePrintMultipleDocuments\Backend\ProductCollection\ButtonCallback;

$GLOBALS['TL_DCA']['tl_iso_product_collection']['config']['dataContainer'] = 'ProductCollectionTable';
$GLOBALS['TL_DCA']['tl_iso_product_collection']['select']['buttons_callback'][] = [ButtonCallback::class, 'addPrintAllDocumentsButton'];
