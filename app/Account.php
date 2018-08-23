<?php

namespace App;
use App\Helpers;
use Carbon\Carbon;

class Account extends BaseModel {

    protected $table = 'accounts';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'owner',
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
    ];


    /**
     * Map between object members and table fields
     *
     * @var array
     */
    protected $maps = [
    ];

    public function setOwnerAttribute(User $user) {
        $this->attributes['id_user_owner'] = $user->id;
    }

    public function owner() {
        return $this->hasOne('App\User','id','id_user_owner');
    }

    public function transactions() {
        return $this->hasMany('App\Transaction','id_account','id');
    }

    protected $connection = 'local';
}
