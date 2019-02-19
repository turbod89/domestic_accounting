<?php

namespace App;
use App\Helpers;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

    public function getReport($detail = 'monthly') {

        $queryTemplate = "
            select
                @year_select
                @month_select
                @day_select
                t.value as value,
                t.value_in as value_in,
                t.value_out as value_out,
                t.num_transactions,
                b.balance
            from
                (
                    select
                        @annual*year(t.transaction_date) as y,
                        @monthly*month(t.transaction_date) as m,
                        @daily*day(t.transaction_date) as d,
                        sum(t.value) as value,
                        sum(if(t.value > 0, t.value, 0)) as value_in,
                        sum(if(t.value < 0, t.value, 0)) as value_out,
                        count(*) num_transactions
                    from raccoon.transactions t
                    inner join raccoon.accounts a on t.id_account = a.id
                    inner join raccoon.users u on u.id = a.id_user_owner
                    where a.id = @id_account
                    group by
                        @annual*year(t.transaction_date)
                        , @monthly*month(t.transaction_date)
                        , @daily*day(t.transaction_date)
                ) as t
            left join
                (
                    select
                        @annual*ids.y as y,
                        @monthly*ids.m as m,
                        @daily*ids.d as d,
                        t.*
                    from (
                        select
                            max(t.id) as id,
                            @annual*year(t.transaction_date) y,
                            @monthly*month(t.transaction_date) m,
                            @daily*day(t.transaction_date) d
                        from raccoon.transactions t
                        where t.id_account = @id_account
                        group by
                            @annual*year(t.transaction_date)
                            , @monthly*month(t.transaction_date)
                            , @daily*day(t.transaction_date)
                    ) ids
                    inner join raccoon.transactions t on t.id = ids.id
                ) as b on b.d = t.d and b.m = t.m and b.y = t.y
            
            order by
                1 asc @year_orderBy @month_orderBy @day_orderBy";

        $query = $queryTemplate;

        $query = str_replace("@id_account", $this->id,$query);

        if ($detail == 'daily') {
            $query = str_replace("@day_select","t.d as `day`,",$query);
            $query = str_replace("@month_select","t.m as `month`,",$query);
            $query = str_replace("@year_select","t.y as `year`,",$query);

            $query = str_replace("@day_orderBy",", `day` asc",$query);
            $query = str_replace("@month_orderBy",", `month` asc",$query);
            $query = str_replace("@year_orderBy",", `year` asc",$query);

            $query = str_replace("@daily","1",$query);
            $query = str_replace("@monthly","1",$query);
            $query = str_replace("@annual","1",$query);
        } else if ($detail == 'monthly') {
            $query = str_replace("@day_select","",$query);
            $query = str_replace("@month_select","t.m as `month`,",$query);
            $query = str_replace("@year_select","t.y as `year`,",$query);

            $query = str_replace("@day_orderBy","",$query);
            $query = str_replace("@month_orderBy",", `month` asc",$query);
            $query = str_replace("@year_orderBy",", `year` asc",$query);

            $query = str_replace("@daily","0",$query);
            $query = str_replace("@monthly","1",$query);
            $query = str_replace("@annual","1",$query);

        } else if ($detail == 'annual') {
            $query = str_replace("@day_select", "", $query);
            $query = str_replace("@month_select", "", $query);
            $query = str_replace("@year_select", "t.y as `year`,", $query);

            $query = str_replace("@day_orderBy", "", $query);
            $query = str_replace("@month_orderBy", "", $query);
            $query = str_replace("@year_orderBy", ", `year` asc", $query);

            $query = str_replace("@daily", "0", $query);
            $query = str_replace("@monthly", "0", $query);
            $query = str_replace("@annual", "1", $query);
        } else {
            $detail = 'total';
            $query = str_replace("@day_select","",$query);
            $query = str_replace("@month_select","",$query);
            $query = str_replace("@year_select","",$query);

            $query = str_replace("@day_orderBy","",$query);
            $query = str_replace("@month_orderBy","",$query);
            $query = str_replace("@year_orderBy","",$query);

            $query = str_replace("@daily","0",$query);
            $query = str_replace("@monthly","0",$query);
            $query = str_replace("@annual","0",$query);
        }

        $report = DB::select($query,[]);

        if ($detail == 'total' && count($report) == 1) {
            return $report[0];
        }

        return $report;

    }

    protected $connection = 'local';
}
