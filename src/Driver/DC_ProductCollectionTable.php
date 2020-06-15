<?php

// Not using a namespace here, because Contao needs to find this class in the classmap.

use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\DC_Table;
use Contao\Environment;
use Contao\Input;
use Contao\Message;
use Contao\SelectMenu;
use Contao\StringUtil;
use Contao\System;
use Isotope\Frontend;
use Isotope\Model\Document;
use Isotope\Model\ProductCollection\Order;
use Psr\Log\LogLevel;

/**
 * Class DC_ProductCollectionTable.
 */
class DC_ProductCollectionTable extends DC_Table {

    /**
     * The 'print all documents' form id.
     *
     * @var string
     */
    protected const PRINT_ALL_DOCUMENTS_FROM_ID = 'isotope_print_all_documents';

    /**
     * The 'print all documents' button id.
     *
     * @var string
     */
    protected const PRINT_ALL_DOCUMENTS_BUTTON_ID = 'print_all_documents';

    /**
     * The 'print all documents' action name.
     *
     * @var string
     */
    protected const PRINT_ALL_DOCUMENTS_ACTION_NAME = 'printAllDocuments';

    /**
     * The zip file name.
     *
     * @var string
     */
    protected const ZIP_FILE_NAME = 'all_documents';

    /**
     * Zip error code map.
     *
     * @var array
     */
    protected const ZIP_ERRORS = [
        ZipArchive::ER_EXISTS => 'File already exists.',
        ZipArchive::ER_INCONS => 'Zip archive inconsistent.',
        ZipArchive::ER_INVAL => 'Invalid argument.',
        ZipArchive::ER_MEMORY => 'Malloc failure.',
        ZipArchive::ER_NOENT => 'No such file.',
        ZipArchive::ER_NOZIP => 'Not a zip archive.',
        ZipArchive::ER_OPEN => 'Can not open file.',
        ZipArchive::ER_READ => 'Read error.',
        ZipArchive::ER_SEEK => 'Seek error.',
    ];

    /**
     * The monolog logger.
     *
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * The url of the current request.
     *
     * @var string
     */
    protected $currentRequestUrl;

    /**
     * The redirect url.
     *
     * @var string
     */
    protected $redirectUrl;

    /**
     * Zip file name.
     *
     * @var string
     */
    protected $zipFileName;

    /**
     * {@inheritdoc}.
     */
    public function __construct($strTable, $arrModule = []) {
        parent::__construct($strTable, $arrModule);

        $this->logger = System::getContainer()->get('monolog.logger.contao');
        $this->currentRequestUrl = Environment::get('request');

        if (Input::post('FORM_SUBMIT') == 'tl_select') {
            if (isset($_POST[self::PRINT_ALL_DOCUMENTS_BUTTON_ID])) {
                // Replace default 'select' action with 'print' action.
                $this->redirect(str_replace('act=select', 'act=' . self::PRINT_ALL_DOCUMENTS_ACTION_NAME, $this->currentRequestUrl));
            }
        }
    }

    /**
     * Returns the document type selection form.
     *
     * If the form has been submitted, it generates the documents,
     * zips them and sends the zip file to the browser.
     *
     * @return string|void
     *   Returns the document-type selection form.
     */
    public function printAllDocuments() {
        $this->redirectUrl = $this->getRedirectUrl();

        // If form id is available, the form has been sent.
        if (Input::post('FORM_SUBMIT') === self::PRINT_ALL_DOCUMENTS_FROM_ID) {
            $this->generateZipFileName();
            $documents = $this->generateDocuments();
            if (!empty($documents)) {
                if ($this->zipDocuments($documents) === TRUE) {
                    $this->sendZipToBrowser();
                }
            }
            else {
                $this->logError('No documents have been created.', __FUNCTION__);
            }
        }
        else {
            /** @var \Contao\SelectMenu $selectMenu */
            $selectMenu = $this->getDocumentTypeSelectMenu();
            $messages = Message::generate();
            Message::reset();

            // TODO: use better solution as this Contao default.
            // Return form.
            return '
<div id="tl_buttons">
 <a href="' . ampersand($this->redirectUrl) . '" class="header_back" title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBT']) . '">' . $GLOBALS['TL_LANG']['MSC']['backBT'] . '</a>
</div>

<h2 class="sub_headline">' . $GLOBALS['TL_LANG']['tl_iso_product_collection']['print_all_documents'][0] . '</h2>' . $messages . '

<form action="' . ampersand($this->currentRequestUrl, true) . '" id="' . self::PRINT_ALL_DOCUMENTS_FROM_ID . '" class="tl_form" method="post">
    <div class="tl_formbody_edit">
        <input type="hidden" name="FORM_SUBMIT" value="' . self::PRINT_ALL_DOCUMENTS_FROM_ID . '">
        <input type="hidden" name="REQUEST_TOKEN" value="' . REQUEST_TOKEN . '">
        
        <div class="tl_tbox block">
          <div class="clr widget">
            ' . $selectMenu->parse() . '
            <p class="tl_help">' . $selectMenu->description . '</p>
          </div>
        </div>
    </div>
    <div class="tl_formbody_submit">
        <div class="tl_submit_container">
            <input type="submit" name="print" id="print" class="tl_submit" alt="" accesskey="s" value="' . StringUtil::specialchars($GLOBALS['TL_LANG']['tl_iso_product_collection']['print']) . '">
        </div>
    </div>
</form>';
        }

    }

    /**
     * Get the document-type select menu.
     *
     * @return \Contao\SelectMenu
     *   The document type select menu.
     */
    protected function getDocumentTypeSelectMenu(): SelectMenu {
        $selectData = [
            'name' => 'document',
            'label' => &$GLOBALS['TL_LANG']['tl_iso_product_collection']['document_choice'],
            'inputType' => 'select',
            'foreignKey' => 'tl_iso_document.name',
            'eval' => ['mandatory' => TRUE]
        ];

        return new SelectMenu(SelectMenu::getAttributesFromDca($selectData, $selectData['name']));
    }

    /**
     * Get the redirect url.
     *
     * @return string
     */
    protected function getRedirectUrl(): string {
        return str_replace('&act=' . self::PRINT_ALL_DOCUMENTS_ACTION_NAME, '', $this->currentRequestUrl);
    }

    /**
     * Get the sessions ids (the selected entities).
     *
     * @return array
     *   The sessions ids.
     */
    protected function getSessionIds(): array {
        /** @var \Contao\Session $sessionObject */
        $sessionObject = System::getContainer()->get('session');
        $session = $sessionObject->all();
        return $session['CURRENT']['IDS'];
    }

    /**
     * Generate an unique zip file name.
     */
    protected function generateZipFileName(): void {
        $timestamp = date('Ymd_Hisv', time());
        $this->zipFileName = self::ZIP_FILE_NAME . '_' . $timestamp . '.zip';
    }

    /**
     * Get the zip file path.
     *
     * @return string
     *   The zip file path.
     */
    protected function getZipFilePath(): string {
        return sys_get_temp_dir() . '/' . $this->zipFileName;
    }

    /**
     * Generate the documents.
     *
     * @return array
     *   Array, which includes the paths to the generated documents.
     */
    protected function generateDocuments(): array {
        $documents = [];
        $ids = $this->getSessionIds();
        foreach ($ids as $id) {

            // As done in \Isotope\Backend\ProductCollection\Callback::printDocument().
            /** @var \Isotope\Model\ProductCollection\Order $objOrder */
            if (($objOrder = Order::findByPk($id)) === NULL) {
                $message = 'Could not find order id.';
                $this->logError($message, __FUNCTION__);
                $this->printErrorMessageAndRedirect($message);
            }

            Frontend::loadOrderEnvironment($objOrder);

            /** @var \Isotope\Interfaces\IsotopeDocument $objDocument */
            if (($objDocument = Document::findByPk(Input::post('document'))) === NULL) {
                $message = 'Could not find document id.';
                $this->logError($message, __FUNCTION__);
                $this->printErrorMessageAndRedirect($message);
            }

            $documents[] = $objDocument->outputToFile($objOrder, sys_get_temp_dir());
        }

        return $documents;
    }

    /**
     * Zip the documents.
     *
     * @param array $documents
     *   The document paths.
     *
     * @return bool
     *   True if successful, false otherwise.
     */
    protected function zipDocuments(array $documents): bool {
        /** @var \ZipArchive $zip */
        $zip = new \ZipArchive;

        $result = $zip->open($this->getZipFilePath(), \ZipArchive::CREATE);
        if ($result === TRUE) {
            foreach ($documents as $document) {
                $zip->addFile($document, basename($document));
            }
            $zip->close();

            return TRUE;
        }
        else {
            $message = 'Error while creating the zip file. Error: ';
            if (isset(self::ZIP_ERRORS[$result])) {
                $message .= self::ZIP_ERRORS[$result];
            }
            else {
                $message .= 'Unknown error.';
            }

            $this->logError($message, __FUNCTION__);
            $this->printErrorMessageAndRedirect($message);
        }

        return FALSE;
    }

    /**
     * Send the zip file to the browser.
     */
    protected function sendZipToBrowser(): void {
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . $this->zipFileName);
        header('Content-Length: ' . filesize($this->getZipFilePath()));
        readfile($this->getZipFilePath());
        exit;
    }

    /**
     * Log error message.
     *
     * @param string $message
     *   The error message.
     * @param string $functionName
     *   The function name.
     */
    protected function logError(string $message, string $functionName): void {
        $this->logger->log(
            LogLevel::ERROR,
            $message,
            ['contao' => new ContaoContext(__CLASS__ . '::' . $functionName, TL_GENERAL)]
        );
    }

    /**
     * Print error message & redirect.
     *
     * @param string $message
     *   The error message.
     */
    protected function printErrorMessageAndRedirect(string $message): void {
        Message::addError($message);
        Controller::redirect($this->redirectUrl);
    }

}
