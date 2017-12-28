<?php

namespace Intra\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomEventGroupModel extends Eloquent
{
    use SoftDeletes;

    protected $table = 'room_event_groups';
    public $timestamps = false;
    protected $fillable = [
        'uid',
        'room_id',
        'from_date',
        'to_date',
        'from_time',
        'to_time',
        'days_of_week',
        'desc'
    ];
}
