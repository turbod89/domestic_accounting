<?php

namespace App;
use App\Helpers\SpreadsheetHelper;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class SantanderTransactionsListTemplate extends TransactionsListTemplate {

    const FIELD_NAMES=[
        [
            'title' => 'Fecha Operación',
            'name' => 'transactionDate',
        ],
        [
            'title' => 'Fecha Valor',
            'name' => 'valueDate',
        ],
        [
            'title' => 'Concepto',
            'name' => 'concept',
        ],
        [
            'title' => 'Importe',
            'name' => 'value',
        ],
        [
            'title' => 'Saldo',
            'name' => 'balance',
        ],
    ];

    private static function getAccountFromSpreadsheet($spreadsheet,$owner) {
        $found = SpreadsheetHelper::searchFirstInCells('Número de Cuenta:', $spreadsheet);

        if (is_null($found)) {
            return null;
        }

        $worksheet = $found['worksheet'];
        $headerCell = $found['cell'];
        $headerColIndex = Coordinate::columnIndexFromString($headerCell->getColumn());
        $colIndex = $headerColIndex + 2;

        $accountName = $worksheet->getCellByColumnAndRow($colIndex, $headerCell->getRow())->getValue();
        $accountName = str_replace(" ", "", $accountName);
        $accountName = trim($accountName);

        $account = Account::firstOrCreate([
            'name' => $accountName,
        ],[
            'owner' => $owner,
        ]);

        return $account;
    }

    public static function getTransactionsListFromFile($fd,$user) {

        $spreadsheet = SpreadsheetHelper::openFile($fd);
        $account = self::getAccountFromSpreadsheet($spreadsheet,$user);

        $data = [];
        foreach (self::FIELD_NAMES as $field) {

            $found = SpreadsheetHelper::searchFirstInCells($field['title'], $spreadsheet);

            if (!is_null($found)) {
                $worksheet = $found['worksheet'];
                $headerCell = $found['cell'];
                $headerColIndex = Coordinate::columnIndexFromString($headerCell->getColumn());

                $highestRow = $worksheet->getHighestRow();

                $colIndex = $headerColIndex;
                for ($rowIndex = $headerCell->getRow() + 1; $rowIndex <= $highestRow ; $rowIndex++) {
                    $value = $worksheet->getCellByColumnAndRow($colIndex, $rowIndex)->getValue();
                    $data[$field['name']][] = $value;
                }

            }
        }

        // check consistency
        $consistent = true;
        for ($i = 1; $i < count(self::FIELD_NAMES) && $consistent; $i++) {
            $consistent = $consistent && count(self::FIELD_NAMES[$i-1]) === count(self::FIELD_NAMES[$i]);
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
            $rowData['transactionDate'] = Carbon::createFromFormat('d/m/Y H', $rowData['transactionDate'].' 00');
            $rowData['valueDate'] = Carbon::createFromFormat('d/m/Y H', $rowData['valueDate'].' 00');

            // parse numbers
            $rowData['value'] = self::string2floatSpanish($rowData['value']);
            $rowData['balance'] = self::string2floatSpanish($rowData['balance']);


            $transaction = new Transaction();
            $transaction->fill($rowData);
            $parsedData[] = $transaction;
        }

        $parsedData = array_reverse($parsedData);
        return $parsedData;
    }

}
