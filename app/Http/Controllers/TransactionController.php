<?php

namespace App\Http\Controllers;

use App\Helpers\SpreadsheetHelper;
use App\SantanderTransactionsListTemplate;
use App\Transaction;
use Illuminate\Http\Request;

class TransactionController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Import transactions from csv
     *
     * @return view
     */
    public function import(Request $request) {
        $files = $request->allFiles();
        foreach ($files as $file) {

            if ($file->isValid()) {
                error_log('File is valid');

                $dstPath = '/tmp/';
                $dstName = 'a.xls';
                $dstFd = $dstPath . $dstName;
                $file->move($dstPath, $dstName);

                $data = SantanderTransactionsListTemplate::getTransactionsListFromFile($dstFd);

                foreach ($data as $transaction) {

                    $actualTransaction = Transaction::where([
                        ['transaction_date',$transaction->transactionDate],
                        ['value_date',$transaction->valueDate],
                        ['concept',$transaction->concept],
                        ['account',$transaction->account],
                        ['value',$transaction->value],
                        ['balance',$transaction->balance],
                    ])->first();

                    if (is_null($actualTransaction)) {
                        // error_log('Transaction with date '.$transaction->transactionDate->format('d/m/Y').' does not exist yet.');
                        $transaction->save();
                    } else {
                        // error_log('Transaction with date '.$transaction->transactionDate->format('d/m/Y').' already exists.');
                    }
                }

            } else {
                error_log('File is invalid');
            }

        }
    }
}
