<?php

namespace Intra\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlexTimeModel extends Eloquent
{
    use SoftDeletes;

    protected $table = 'flextimes';
    public $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;
    public $dateFormat = 'Y-m-d H:i:s';
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    ];
}
