<?php

namespace App\Http\Controllers;

use App\Account;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    public static function isUserAccountOrError(Request $request, $account, Callable $func) {

        if ($account instanceof Account) {
            // Do nothing
        } else {
            $account = Account::find($account);
        }

        if ( !$account || $account->owner->id !== $request->user()->id) {
            return response()->json([
                "error" => [
                    "code" => 1,
                    "message" => "Invalid account.",
                ]
            ]);
        }

        return $func($request, $account);
    }

    public static function reportToExcel($report) {
        $spreadsheet = new Spreadsheet();
        $worksheet = $spreadsheet->getActiveSheet();

        if (isset($report[0])) {

            $colIndex = 2;
            $rowIndex = 2;

            $fields = [
                [
                    "concept" => "date",
                    "setHeader" => function (Worksheet $worksheet, $report) use ($colIndex, $rowIndex) {
                        $cell = $worksheet->getCellByColumnAndRow($colIndex + 0, $rowIndex + 0);
                        $cell->setValue('Date');
                        $cell->getStyle()->getFont()->setBold(true);
                        $cell->setDataType(DataType::TYPE_STRING);
                    },
                    "setBody" => function (Worksheet $worksheet, $transaction, $i, $report) use ($colIndex, $rowIndex) {
                        $year = $transaction->year;
                        $month = isset($transaction->month) ? $transaction->month : 12;
                        $day = isset($transaction->day) ? $transaction->day : date("t", strtotime("$year-$month-01"));
                        $date = min(Carbon::create($year,$month,$day),Carbon::now());
                        $excelDateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel( $date );

                        $cell = $worksheet->getCellByColumnAndRow($colIndex + 0, $rowIndex + 1 + $i);
                        $cell->setValue($excelDateValue);
                        $cell->getStyle()
                            ->getNumberFormat()
                            ->setFormatCode(
                                NumberFormat::FORMAT_DATE_DDMMYYYY
                            );
                    },

                ],
                [
                    "concept" => "value_in",
                    "setHeader" => function (Worksheet $worksheet, $report) use ($colIndex, $rowIndex) {
                        $cell = $worksheet->getCellByColumnAndRow($colIndex + 2, $rowIndex + 0);
                        $cell->setValue('In');
                        $cell->getStyle()->getFont()->setBold(true);
                        $cell->setDataType(DataType::TYPE_STRING);
                    },
                    "setBody" => function (Worksheet $worksheet, $transaction, $i, $report) use ($colIndex, $rowIndex) {
                        $cell = $worksheet->getCellByColumnAndRow($colIndex + 2, $rowIndex + 1 + $i);
                        $cell->setValue($transaction->value_in);
                        $cell->getStyle()->getFont()->setColor( new Color(Color::COLOR_DARKGREEN));
                        $cell->setDataType(DataType::TYPE_NUMERIC);
                    },

                ],
                [
                    "concept" => "value_out",
                    "setHeader" => function (Worksheet $worksheet, $report) use ($colIndex, $rowIndex) {
                        $cell = $worksheet->getCellByColumnAndRow($colIndex + 3, $rowIndex + 0);
                        $cell->setValue('Out');
                        $cell->getStyle()->getFont()->setBold(true);
                        $cell->setDataType(DataType::TYPE_STRING);
                    },
                    "setBody" => function (Worksheet $worksheet, $transaction, $i, $report) use ($colIndex, $rowIndex) {
                        $cell = $worksheet->getCellByColumnAndRow($colIndex + 3, $rowIndex + 1 + $i);
                        $cell->setValue(-$transaction->value_out);
                        $cell->getStyle()->getFont()->setColor( new Color(Color::COLOR_DARKRED));
                        $cell->setDataType(DataType::TYPE_NUMERIC);
                    },

                ],
                [
                    "concept" => "num_transactions",
                    "setHeader" => function (Worksheet $worksheet, $report) use ($colIndex, $rowIndex) {
                        $cell = $worksheet->getCellByColumnAndRow($colIndex + 5, $rowIndex + 0);
                        $cell->setValue('Number Transactions');
                        $cell->getStyle()->getFont()->setBold(true);
                        $cell->setDataType(DataType::TYPE_STRING);
                    },
                    "setBody" => function (Worksheet $worksheet, $transaction, $i, $report) use ($colIndex, $rowIndex) {
                        $cell = $worksheet->getCellByColumnAndRow($colIndex + 5, $rowIndex + 1 + $i);
                        $cell->setValue($transaction->num_transactions);
                        $cell->setDataType(DataType::TYPE_NUMERIC);
                    },

                ],
                [
                    "concept" => "value",
                    "setHeader" => function (Worksheet $worksheet, $report) use ($colIndex, $rowIndex) {
                        $cell = $worksheet->getCellByColumnAndRow($colIndex + 6, $rowIndex + 0);
                        $cell->setValue('Value');
                        $cell->getStyle()->getFont()->setBold(true);
                        $cell->setDataType(DataType::TYPE_STRING);
                    },
                    "setBody" => function (Worksheet $worksheet, $transaction, $i, $report) use ($colIndex, $rowIndex) {
                        $cell = $worksheet->getCellByColumnAndRow($colIndex + 6, $rowIndex + 1 + $i);
                        $cell->setValue($transaction->value);
                        $cell->setDataType(DataType::TYPE_NUMERIC);
                    },

                ],
                [
                    "concept" => "balance",
                    "setHeader" => function (Worksheet $worksheet, $report) use ($colIndex, $rowIndex) {
                        $cell = $worksheet->getCellByColumnAndRow($colIndex + 7, $rowIndex + 0);
                        $cell->setValue('Balance');
                        $cell->getStyle()->getFont()->setBold(true);
                        $cell->setDataType(DataType::TYPE_STRING);
                    },
                    "setBody" => function (Worksheet $worksheet, $transaction, $i, $report) use ($colIndex, $rowIndex) {
                        $cell = $worksheet->getCellByColumnAndRow($colIndex + 7, $rowIndex + 1 + $i);
                        $cell->setValue($transaction->balance);
                        $cell->setDataType(DataType::TYPE_NUMERIC);
                    },

                ],

                [
                    "concept" => "check_balance",
                    "setHeader" => function (Worksheet $worksheet, $report) use ($colIndex, $rowIndex) {
                        $cell = $worksheet->getCellByColumnAndRow($colIndex + 8, $rowIndex + 0);
                        $cell->setValue('Adjust');
                        $cell->getStyle()->getFont()->setBold(true);
                        $cell->setDataType(DataType::TYPE_STRING);
                    },
                    "setBody" => function (Worksheet $worksheet, $transaction, $i, $report) use ($colIndex, $rowIndex) {
                        if ($i <= 0) {
                            return;
                        }

                        $cell = $worksheet->getCellByColumnAndRow($colIndex + 8, $rowIndex + 1 + $i);
                        $r = $rowIndex + 1 + $i;
                        $rm = $r -1 ;
                        $cell->setValue("=IF(ROUND(I\$$r - H\$$r - I\$$rm,2) > 0.005, \"\" , ROUND(I\$$r - H\$$r - I\$$rm, 2 ))");
                        $cell->getCalculatedValue();
                        //$cell->setDataType(DataType::TYPE_STRING);
                    },

                ],
            ];

            // headers
            foreach ($fields as $j => $field) {
                $field['setHeader']($worksheet,$report);
            }
            $worksheet->setAutoFilterByColumnAndRow(2,2,10,2);

            // body
            foreach ($report as $i => $transaction) {
                foreach ($fields as $j => $field) {
                    $field['setBody']($worksheet,$transaction,$i,$report);
                }
            }


        } else {

        }

        return $spreadsheet;
    }

    public function getReport(Request $request, $idAccount) {
        return self::isUserAccountOrError($request, $idAccount,
                function ($request,Account $account) {
                    $detail = $request->input('detail','monthly');
                    $output = $request->input('output','json');

                    $report = $account->getReport($detail);
                    if ($output === 'excel') {
                        $spreadsheet = self::reportToExcel($report);
                        $spreadsheet->getProperties()
                            ->setCreator("Raccoon Domestic Accounting")
                            ->setLastModifiedBy("Raccoon Domestic Accounting")
                            ->setTitle("Report {$account->name} $detail")
                            ->setSubject("Report {$account->name} $detail")
                            ->setDescription(
                                "Report {$account->name} $detail"
                            )
                            ->setKeywords("office 2007 openxml php")
                            //->setCategory("")
                        ;
                        $path = "report_{$account->name}_$detail.xlsx";
                        $writer = new Xlsx($spreadsheet);
                        $writer->save($path);
                        return response()->download($path);
                    } else {
                        return response()->json($report);
                    }
                }
            );
    }

}
