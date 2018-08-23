<?php

namespace App\Http\Controllers;

use App\Helpers\SpreadsheetHelper;
use App\IngTransactionsListTemplate;
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
        $user = $request->user();

        $files = $request->allFiles();
        foreach ($files as $file) {

            if (!$file->isValid()) {
                error_log('File is invalid');
            } else if ($file->getClientMimeType() !== 'application/octet-stream') {
                error_log('Invalid mime type: '.$file->getClientMimeType());
            } else if (!in_array($file->getClientOriginalExtension(),['xlsx','xls'])) {
                error_log('Invalid extension: '.$file->getClientOriginalExtension());
            } else {

                $dstPath = '/tmp/';
                $dstName = $file->getClientOriginalName();
                $dstFd = $dstPath . $dstName;
                $file->move($dstPath, $dstName);

                $data = [];
                if (preg_match('/santander/i',$file->getClientOriginalName())) {
                    error_log('Santander');
                    $data = SantanderTransactionsListTemplate::getTransactionsListFromFile($dstFd,$user);
                } else if (preg_match('/ing.?.?direct/i',$file->getClientOriginalName())) {
                    error_log('ing direct');
                    $data = IngTransactionsListTemplate::getTransactionsListFromFile($dstFd,$user);
                } else {
                    error_log('Not known entity');
                }

                foreach ($data as $transaction) {

                    $actualTransaction = Transaction::where([
                        ['transaction_date',$transaction->transactionDate],
                        ['value_date',$transaction->valueDate],
                        ['concept',$transaction->concept],
                        ['id_account',$transaction->idAccount],
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

            }

        }

        return $this->getAll($request);
    }

    public function getAll(Request $request) {

        $user = $request->user();
        $accounts = $user->accounts;

        $accountsArray = [];
        foreach($accounts as $account ) {
            $accountArray = $account->toArray();
            $transactionsArray = $account->transactions->toArray();
            $accountArray['transactions'] = $transactionsArray;
            $accountsArray[] = $accountArray;
        }

        return response()->json($accountsArray);
    }
}
