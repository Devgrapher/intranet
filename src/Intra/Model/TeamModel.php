<?php

namespace Intra\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class TeamModel extends Eloquent
{
    protected $table = 'teams';
    public $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    public $dateFormat = 'Y-m-d H:i:s';
    protected $dates = ['created_at', 'updated_at'];

    protected $fillable = [
        'uid',
        'manager_uid',
        'keeper_uid',
        'start_date',
        'end_date',
        'start_time',
        'weekdays',
        'phone_emergency',
    ];
}
