<?php

namespace App\Http\Controllers;

use App\Account;
use Illuminate\Http\Request;

class AccountController extends BaseController
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

    public function getReport(Request $request, $idAccount) {
        $user = $request->user();

        $account = Account::find($idAccount);

        if ( !$account || $account->owner->id !== $user->id) {
            return response()->json([
                "error" => [
                    "code" => 1,
                    "message" => "Invalid account id.",
                ]
            ]);
        }

        $detail = $request->input('detail','monthly');

        $report = $account->getReport($detail);
        return response()->json($report);
    }
}
