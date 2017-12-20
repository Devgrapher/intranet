<?php

namespace Intra\Model;

use Illuminate\Database\Eloquent\Model as Eloquent;

class RoomModel extends Eloquent
{
    protected $table = 'rooms';
    public $timestamps = false;
    protected $fillable = [
        'type',
        'name',
        'is_visible',
    ];
}
