<?php
/**
 * @copyright  Copyright (c) 2016, Net Inventors GmbH
 * @category   Shopware
 * @author     hrombach
 */

namespace NetiPhpExcel;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;

class NetiPhpExcel extends Plugin
{
    /**
     * @param InstallContext $context
     * @throws \Exception
     */
    public function install(InstallContext $context)
    {
        if (! is_readable(__DIR__ . DIRECTORY_SEPARATOR . 'vendor')) {
            throw new \Exception('Please run "composer install" before you install the plugin!');
        }

        parent::install($context);
    }
}