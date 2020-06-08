<?php

namespace MarcoSimbuerger\IsotopePrintMultipleDocuments\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use MarcoSimbuerger\IsotopePrintMultipleDocuments\IsotopePrintMultipleDocuments;

/**
 * Class Plugin.
 *
 * @package MarcoSimbuerger\IsotopePrintMultipleDocuments\ContaoManager
 */
class Plugin implements BundlePluginInterface {

    /**
     * {@inheritdoc}.
     */
    public function getBundles(ParserInterface $parser) {
        return [
            BundleConfig::create(IsotopePrintMultipleDocuments::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                    'isotope',
                ]),
        ];
    }
}
