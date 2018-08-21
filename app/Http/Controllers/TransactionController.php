<?php

namespace App\Http\Controllers;

use App\Helpers\SpreadsheetHelper;
use App\SantanderTransactionsListTemplate;
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
            error_log(print_r($file,true));
            error_log($file->getPathName());

            if ($file->isValid()) {
                error_log('File is valid');
                $dstPath = '/tmp/';
                $dstName = 'a.xls';
                $dstFd = $dstPath . $dstName;
                $file->move($dstPath, $dstName);

                $data = SantanderTransactionsListTemplate::getTransactionsListFromFile($dstFd);
                foreach ($data as $transaction) {
                    $transaction->save();
                }

            } else {
                error_log('File is invalid');
            }

        }
    }
}
