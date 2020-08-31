<?php

namespace MarcoSimbuerger\IsotopePrintMultipleDocumentsBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use MarcoSimbuerger\IsotopePrintMultipleDocumentsBundle\ContaoIsotopePrintMultipleDocumentsBundle;

/**
 * Class Plugin.
 *
 * @package MarcoSimbuerger\IsotopePrintMultipleDocumentsBundle\ContaoManager
 */
class Plugin implements BundlePluginInterface {

    /**
     * {@inheritdoc}.
     */
    public function getBundles(ParserInterface $parser) {
        return [
            BundleConfig::create(ContaoIsotopePrintMultipleDocumentsBundle::class)
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                    'isotope',
                ]),
        ];
    }
}
