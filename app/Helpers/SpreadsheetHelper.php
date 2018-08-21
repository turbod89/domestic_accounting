<?php

/**
 *  Author: Daniel Torres
 *
 */

namespace App\Helpers;

use PhpOffice\PhpSpreadsheet\Reader;

class SpreadsheetHelper extends BaseHelper {

    /**
     * Create a new helper instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public static function openFile($fd) {
        $reader = new Reader\Xlsx();
        $reader->setReadDataOnly(true);
        return $reader->load($fd);
    }

    /**
     * Search a value in document cell's
     *
     * @param $searchValue
     * @param $spreadsheet
     * @return array of ['worksheet' => worksheet, 'cell' => cell]
     */
    public static function searchInCells($searchValue, $spreadsheet) {
        $foundInCells = array();
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);
                foreach ($cellIterator as $cell) {
                    if ($cell->getValue() == $searchValue) {
                        $foundInCells[] = [
                            'worksheet' => $worksheet,
                            'cell' => $cell,
                        ];
                    }
                }
            }
        }

        return $foundInCells;
    }

    /**
     * Search a value once in document cell's
     *
     * @param $searchValue
     * @param $spreadsheet
     * @return array of ['worksheet' => worksheet, 'cell' => cell]
     */
    public static function searchFirstInCells($searchValue, $spreadsheet) {
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            foreach ($worksheet->getRowIterator() as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(true);
                foreach ($cellIterator as $cell) {
                    if ($cell->getValue() == $searchValue) {
                        return [
                            'worksheet' => $worksheet,
                            'cell' => $cell,
                        ];
                    }
                }
            }
        }

        return null;
    }

}