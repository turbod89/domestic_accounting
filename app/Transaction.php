<?php

namespace App;
use App\Helpers;
use Carbon\Carbon;

class Transaction extends BaseModel {

    protected $table = 'transactions';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'transactionDate',
        'valueDate',
        'concept',
        'import',
        'balance',
    ];

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
        'transaction_date',
        'value_date',
    ];


    /**
     * Map between object members and table fields
     *
     * @var array
     */
    protected $maps = [
    ];

    public function setTransactionDateAttribute($value) {
        $this->attributes['transaction_date'] = new Carbon($value);
    }


    public function setValueDateAttribute($value) {
        $this->attributes['value_date'] = new Carbon($value);
    }



    protected $connection = 'local';
}
