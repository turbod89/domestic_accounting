<?php

namespace App;
use App\Helpers\SpreadsheetHelper;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class TransactionsListTemplate extends BaseModel {

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


    protected static function string2floatSpanish($value) {
        return (float) str_replace(',','.',str_replace('.','',$value));
    }

    protected static function string2floatEnglish($value) {
        return (float) str_replace(',','',$value);
    }

}
