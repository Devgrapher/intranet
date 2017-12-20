<?php

namespace Intra\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomEventModel extends Eloquent
{
    use SoftDeletes;

    protected $table = 'room_events';
    public $timestamps = false;
    protected $fillable = [
        'uid',
        'room_id',
        'desc',
        'from',
        'to',
    ];
}
