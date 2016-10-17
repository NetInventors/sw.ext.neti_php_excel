<?php
/**
 * @copyright  Copyright (c) 2016, Net Inventors GmbH
 * @category   Shopware
 * @author     rubyc
 */

namespace NetiPhpExcel\Service;


use Gaufrette\Exception;

class PhpExcel
{
    const FORMAT_EXCEL = 1;
    const FORMAT_CSV   = 2;

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

    /**
     * This function tries to determine the delimiter counting the occurrences in the first row.
     * The quality of the result depends on the contained values in the first row, it might nox be correct in any case.
     *
     * @param string $csvFile
     * @return string
     */
    public function detectDelimiter($csvFile)
    {
        $delimiters = [
            ';' => 0,
            ',' => 0,
            "\t" => 0,
            "|" => 0
        ];

        $handle = fopen($csvFile, "r");
        $firstLine = fgets($handle);
        fclose($handle);
        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($firstLine, $delimiter));
        }

        return array_search(max($delimiters), $delimiters);
    }

    /**
     * @param array $data
     * If $data contains an associative array, the keys will be set as headlines in the first row.
     * @param string $filename
     * @param int $format
     * self::FORMAT_CSV or self::FORMAT_EXCEL
     * @param string $delimiter
     */
    public function exportFunction($data, $filename, $format = self::FORMAT_CSV, $delimiter = ',')
    {
        $phpExcel = $this->getPhpExcel();
        $phpExcel->setActiveSheetIndex(0);

        if ($this->isAssoc($data[0])) {
            $phpExcel->getActiveSheet()->fromArray(array_keys(reset($data)), null, 'A1');
            $phpExcel->getActiveSheet()->fromArray($data, null, 'A2');
        } else {
            $phpExcel->getActiveSheet()->fromArray($data, null, 'A1');
        }

        $filename = $filename . '.' . $this->getExtension($format);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($phpExcel, $this->getWriterType($format));
        if (self::FORMAT_CSV === $format) {
            $objWriter->setDelimiter($delimiter);
        }

        if (! (empty($objWriter)) && $objWriter instanceof \PHPExcel_Writer_IWriter) {
            $objWriter->save('php://output');
        }
        exit;
    }

    /**
     * @param string $filename
     * @return array
     * @throws \Exception
     */
    public function getArrayFromFile($filename)
    {
        if (! is_readable($filename)) {
            throw new \Exception('File does not exist or is not readable: ' . $filename);
        }

        $this->getPhpExcel();
        $inputFileType = \PHPExcel_IOFactory::identify($filename);
        $objReader = \PHPExcel_IOFactory::createReader($inputFileType);

        // detect and set delimiter
        $delimiter = $this->detectDelimiter($filename);
        $objReader->setDelimiter($delimiter);
        $objPHPExcel = $objReader->load($filename);

        $worksheet = $objPHPExcel->getActiveSheet();
        $delimiter = null;

        $rows = [];
        foreach ($worksheet->getRowIterator() as $rowData) {
            $row = [];
            $cellIterator = $rowData->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
            foreach ($cellIterator as $cell) {
                /** @var \PHPExcel_Cell $cell */
                if (!is_null($cell)) {
                    $row[] = $cell->getValue();
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Transforms the imported numeric arrays into a associative arrays.
     * The keys will be taken from the first row of the $rows array.
     *
     * @param array $rows
     * @return array
     */
    public function createAssociativeArray($rows)
    {
        $results = [];
        $firstRow = true;
        foreach ($rows as $row) {
            if ($firstRow) {
                $firstRow = false;
                continue;
            }

            $result = [];
            foreach ($row as $key => $value) {
                $index = $rows[0][$key];
                if (null === $index || '' === $index) {
                    continue;
                }
                $result[$index] = $value;
            }
            $results[] = $result;
        }

        return $results;
    }

    /**
     * @param array $arr
     * @return boolean
     */
    private function isAssoc(array $arr)
    {
        if ([] === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * @param int $format
     * @return string
     */
    private function getExtension($format)
    {
        switch ($format) {
            case self::FORMAT_EXCEL:
                return 'xls';
            case self::FORMAT_CSV:
                return 'csv';
            default:
                return 'unknown';
        }
    }

    /**
     * @param int $format
     * @return string
     */
    private function getWriterType($format)
    {
        switch ($format) {
            case self::FORMAT_EXCEL:
                return 'Excel5';
            case self::FORMAT_CSV:
                return 'CSV';
            default:
                return 'unknown';
        }
    }
}