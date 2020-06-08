<?php

$GLOBALS['TL_DCA']['tl_iso_product_collection']['config']['dataContainer'] = 'ProductCollectionTable';
$GLOBALS['TL_DCA']['tl_iso_product_collection']['select']['buttons_callback'][] = ['\MarcoSimbuerger\IsotopePrintMultipleDocuments\Backend\ProductCollection\ButtonCallback', 'addPrintAllDocumentsButton'];
