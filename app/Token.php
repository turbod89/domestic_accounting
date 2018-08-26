<?php

namespace App;
use App\Helpers;
use Carbon\Carbon;

class Token extends BaseModel {

    private static $session = null;

    protected $table = 'tokens';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'value',
        'expire_at',
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
        'expire_at',
    ];


    /**
     * Map between object members and table fields
     *
     * @var array
     */
    protected $maps = [
    ];

    public function setUserAttribute(User $user) {
        $this->attributes['id_user'] = $user->id;
    }

    public function setApiAttribute(int $i) {
        $this->attributes['id_api'] = $i;
    }

    public function getApiAttribute() {
        return $this->id_api;
    }

    public function user() {
        return $this->hasOne('App\User','id','id_user');
    }

    public static function session($session = null) {

        if (!is_null($session)) {
            self::$session = $session;
        }

        return is_null(self::$session) ? new Token() : self::$session;
    }

    protected $connection = 'local';
}
