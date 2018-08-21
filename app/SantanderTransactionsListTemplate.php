<?php

namespace App;
use App\Helpers\SpreadsheetHelper;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class SantanderTransactionsListTemplate extends BaseModel {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];

    protected $dates = [
        self::CREATED_AT,
        self::UPDATED_AT,
    ];


    /**
     * Map between object members and table fields
     *
     * @var array
     */
    protected $maps = [
    ];

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
            'name' => 'import',
        ],
        [
            'title' => 'Saldo',
            'name' => 'balance',
        ],
    ];

    public static function getTransactionsListFromFile($fd) {

        $spreadsheet = SpreadsheetHelper::openFile($fd);

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

            // parse dates

            $rowData['transactionDate'] = Carbon::createFromFormat('d/m/Y H', $rowData['transactionDate'].' 00');
            $rowData['valueDate'] = Carbon::createFromFormat('d/m/Y H', $rowData['valueDate'].' 00');

            //
            $transaction = new Transaction();
            $transaction->fill($rowData);
            $parsedData[] = $transaction;
        }

        $parsedData = array_reverse($parsedData);
        return $parsedData;
    }

}
