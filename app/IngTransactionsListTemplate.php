<?php

namespace App;
use App\Helpers\SpreadsheetHelper;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class IngTransactionsListTemplate extends TransactionsListTemplate {

    const FIELD_NAMES=[
        [
            'title' => 'FECHA VALOR',
            'name' => 'transactionDate',
            'coordinate' => 'A6',
        ],
        [
            'title' => 'FECHA VALOR',
            'name' => 'valueDate',
            'coordinate' => 'A6',
        ],
        [
            'title' => 'DESCRIPCIÓN',
            'name' => 'concept',
            'coordinate' => 'D6',
        ],
        [
            'title' => 'IMPORTE (€)',
            'name' => 'value',
            'coordinate' => 'G6',
        ],
        [
            'title' => 'SALDO (€)',
            'name' => 'balance',
            'coordinate' => 'H6',
        ],
    ];

    private static function getAccountFromSpreadsheet(Spreadsheet $spreadsheet,User $owner) {
        $worksheet = $spreadsheet->getActiveSheet();
        $accountName = $worksheet->getCellByColumnAndRow(4, 2)->getValue();
        $accountName = str_replace(" ", "", $accountName);
        $accountName = trim($accountName);

        $account = Account::firstOrCreate([
            'name' => $accountName,
        ],[
            'owner' => $owner,
        ]);

        return $account;
    }

    public static function getTransactionsListFromFile($fd, User $user) {

        $spreadsheet = SpreadsheetHelper::openFile($fd);
        $account = self::getAccountFromSpreadsheet($spreadsheet, $user);

        $data = [];
        $worksheet = $spreadsheet->getActiveSheet();

        foreach (self::FIELD_NAMES as $field) {

            $headerCell = $worksheet->getCell($field['coordinate']);
            $headerColIndex = Coordinate::columnIndexFromString($headerCell->getColumn());

            $highestRow = $worksheet->getHighestRow();

            $colIndex = $headerColIndex;
            for ($rowIndex = $headerCell->getRow() + 1; $rowIndex <= $highestRow ; $rowIndex++) {
                $value = $worksheet->getCellByColumnAndRow($colIndex, $rowIndex)->getValue();
                $data[$field['name']][] = $value;
            }

        }

        // check consistency
        $consistent = true;
        for ($i = 1; $i < count(self::FIELD_NAMES) && $consistent; $i++) {
            $consistent = $consistent && count($data[self::FIELD_NAMES[$i-1]['name']]) === count($data[self::FIELD_NAMES[$i]['name']]);
        }

        if (!$consistent) {
            error_log('Inconsistency in data');
            return null;
        }

        $parsedData = [];
        foreach ($data[self::FIELD_NAMES[0]['name']] as $rowIndex => $value) {
            $rowData = [];
            foreach (self::FIELD_NAMES as $field) {
                $rowData[$field['name']] = $data[$field['name']][$rowIndex];
            }
            $rowData['account'] = $account;
            // parse strings
            $rowData['concept'] = trim($rowData['concept']);
            // parse dates
            $rowData['transactionDate'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rowData['transactionDate']);
            $rowData['transactionDate'] = Carbon::instance($rowData['transactionDate']);
            $rowData['valueDate'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rowData['valueDate']);
            $rowData['valueDate'] = Carbon::instance($rowData['valueDate']);

            // parse numbers
            $rowData['value'] = self::string2floatEnglish($rowData['value']);
            $rowData['balance'] = self::string2floatEnglish($rowData['balance']);

            $transaction = new Transaction();
            $transaction->fill($rowData);
            $parsedData[] = $transaction;
        }

        $parsedData = array_reverse($parsedData);
        return $parsedData;
    }

}
