<?php
/**
 * @copyright  Copyright (c) 2016, Net Inventors GmbH
 * @category   Shopware
 * @author     rubyc
 */

namespace NetiPhpExcel\Service;


class PhpExcel
{
    /**
     * @var \PHPExcel|boolean
     */
    protected $phpExcel;

    /**
     * @return bool|\PHPExcel
     */
    public function getPhpExcel()
    {
        if (null === $this->phpExcel) {
            if (! class_exists('PHPExcel')) {
                require_once __DIR__ . '/../vendor/phpoffice/phpexcel/Classes/PHPExcel.php';
            }

            $this->phpExcel = new \PHPExcel();
        } else {
            $this->phpExcel = false;
        }

        return $this->phpExcel;
    }
}